<?php

if (!defined('ABSPATH')) {
    exit;
}

class WC_Ginger_Callback extends WC_Ginger_BankGateway
{
    public function __construct()
    {
        $this->id = 'ginger';
        $this->icon = false;
        $this->has_fields = false;
        $this->method_title = __(WC_Ginger_BankConfig::BANK_LABEL, WC_Ginger_BankConfig::BANK_PREFIX);
        $this->method_description = __(WC_Ginger_BankConfig::BANK_LABEL ." - Library", WC_Ginger_BankConfig::BANK_PREFIX);

        parent::__construct();
    }

    public function ginger_handle_callback()
    {
        if (!sanitize_text_field(filter_input(INPUT_GET,'order_id',FILTER_SANITIZE_STRING))) // hence it's webhook
        {
            $input = json_decode(file_get_contents("php://input"), true);
            if (!in_array($input['event'], array("status_changed"))) die("Only work to do if the status changed");
            $gingerOrderID = $input['order_id'];
            $gingerOrder = $this->ginger_handle_get_order($gingerOrderID);
            $order = new WC_Order($gingerOrder['merchant_order_id']);
            $gingerOrderIDMeta = get_post_meta($gingerOrder['merchant_order_id'], WC_Ginger_BankConfig::BANK_PREFIX.'_order_id', true);

            if($gingerOrder['id'] !== $gingerOrderIDMeta) exit;

            if ($gingerOrder['status'] == 'completed')
            {
                if (version_compare(get_option('woocommerce_version', 'Unknown'), '2.2.0', '>=')) {
                    $order->payment_complete($gingerOrderID);
                } else {
                    $order->payment_complete();
                }
                exit;
            }

            if (isset($gingerOrder['transactions']['flags']['has-captures']))
            {
                if ($order->get_status() == 'processing')
                {
                    $order->update_status('shipped', 'Order updated to shipped, transactions was captured', false);
                }
                exit;
            }

            $order->update_status($this->ginger_get_store_status($gingerOrder['status']));
            exit;
        }

        $gingerOrder = $this->ginger_handle_get_order(sanitize_text_field(filter_input(INPUT_GET,'order_id',FILTER_SANITIZE_STRING)));
        $order = new WC_Order($gingerOrder['merchant_order_id']);

        if ($gingerOrder['status'] == 'completed' || $gingerOrder['status'] == 'processing')
        {
            header("Location: ".$this->get_return_url($order));
            exit;
        }

        wc_add_notice(__('There was a problem processing your transaction. ' .current($gingerOrder['transactions'])['customer_message'], WC_Ginger_BankConfig::BANK_PREFIX), 'error');
        if ($this->get_option('failed_redirect') == 'cart') {
            $url = $order->get_cancel_order_url();
        } else {
            $url = $order->get_checkout_payment_url();
        }

        header("Location: ".str_replace("&amp;", "&", $url));
        exit;
    }

    public function ginger_handle_get_order($gingerOrderID): array
    {
        // potentially exists 3 different API keys that can fetch the order
        // first try with standard API key
        try {
            if ($this->gingerClient) return $this->gingerClient->getOrder($gingerOrderID);
        } catch (Exception $exception) {
            $exceptionMessage = $exception->getMessage();
        }

        // second try with api key from Klarna
        $this->gingerClient = WC_Ginger_Clientbuilder::gingerBuildClient(WC_Ginger_BankConfig::BANK_PREFIX.'_klarna-pay-later');
        try {
            if ($this->gingerClient) return $this->gingerClient->getOrder($gingerOrderID);
        } catch (Exception $exception) {
            $exceptionMessage = $exception->getMessage();
        }

        // third try with api key from Afterpay
        $this->gingerClient = WC_Ginger_Clientbuilder::gingerBuildClient(WC_Ginger_BankConfig::BANK_PREFIX.'_afterpay');
        try {
            if ($this->gingerClient) return $this->gingerClient->getOrder($gingerOrderID);
        } catch (Exception $exception) {
            $exceptionMessage = $exception->getMessage();
        }
        $errorMessage = $exceptionMessage ?? "COULD NOT GET ORDER";
        die($errorMessage);
    }

    /**
     * Function ginger_get_store_status
     *
     * @param $gingerOrderStatus
     * @return string
     */
    public function ginger_get_store_status($gingerOrderStatus): string
    {
        $maps_statuses = [
            'new' => 'pending',
            'processing' => 'pending',
            'error' => 'failed',
            'expired' => 'cancelled',
            'cancelled' => 'cancelled',
            'see-transactions' => 'on-hold'
        ];
        return $maps_statuses[$gingerOrderStatus];
    }
}
