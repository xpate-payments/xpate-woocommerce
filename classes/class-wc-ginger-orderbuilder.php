<?php

if (!defined('ABSPATH')) {
    exit;
}

class WC_Ginger_Orderbuilder extends WC_Ginger_Gateway
{

    private $billingAddress;
    private $shippingAddress;

    /**
     * Function builds an order
     * @return array
     * @throws Exception
     */
    public function gingerGetBuiltOrder(): array
    {
        $order = [];

        $order['merchant_order_id'] = $this->gingerGetMerchantOrderID();
        $order['customer'] = $this->gingerGetCustomerInfo();
        $order['currency'] = $this->gingerGetCurrency();
        $order['extra'] = $this->gingerGetExtra();
        $order['amount'] = $this->gingerGetAmount();
        $order['description'] = $this->gingerGetOrderDescription();
        $order['return_url'] = $this->gingergetReturnUrl();
        $order['webhook_url'] = $this->gingerGetWebhookUrl();
        $order['order_lines'] = $this->gingerGetOrderLines($this->woocommerceOrder);

        if (!$this instanceof GingerHostedPaymentPage) //HPP order must not contains transaction field
        {
            $order['transactions'][] = $this->gingerGetTransactions();;
        }

        return $order;

    }

    /**
     * Function returns selected ideal issuer
     * @return string|null
     */
    public function gingerGetSelectedIssuer(): ?string
    {
        return WC_Ginger_Helper::gingerGetCustomPaymentField("ginger_ideal_issuer_id");
    }

    /**
     * Function returns transaction array
     * @return array
     * @throws Exception
     */
    public function gingerGetTransactions(): array
    {
        return array_filter([
            'payment_method' => $this->gingerGetPaymentMethod(),
            'payment_method_details' => $this->gingerGetPaymentMethodDetails()
        ]);
    }

    /**
     * @return array|string[]
     * @throws Exception
     */
    public function gingerGetPaymentMethodDetails(): array
    {
        $paymentMethodDetails = [];

        //uses for ideal
        if ($this instanceof GingerIssuers)
        {
            $paymentMethodDetails['issuer_id'] = $this->gingerGetSelectedIssuer();
            return $paymentMethodDetails;
        }

        //uses for afterpay
        if ($this instanceof GingerTermsAndConditions)
        {
            $termsAndConditionFlag = WC_Ginger_Helper::gingerGetCustomPaymentField('toc');
            if ($termsAndConditionFlag)
            {
                $paymentMethodDetails = [
                    'verified_terms_of_service' => true,
                ];
            }
            return $paymentMethodDetails;

        }

        return $paymentMethodDetails;

    }



    /**
     * Function returns chosen payment method
     * @return string|string[]
     */
    public function gingerGetPaymentMethod(): string
    {
        return str_replace(WC_Ginger_BankConfig::BANK_PREFIX.'_', '', $this->id);
    }

    /**
     * Function returns extra fields
     * @return array
     */
    public function gingerGetExtra(): array
    {
        return [
            'user_agent' => $this->gingerGetUserAgent(),
            'platform_name' => $this->gingerGetPlatformName(),
            'platform_version' => $this->gingerGetPlatformVersion(),
            'plugin_name' => $this->gingerGetPluginName(),
            'plugin_version' => $this->gingerGetPluginVersion()
        ];
    }
    public function gingerGetPluginVersion(): string
    {
        return GINGER_PLUGIN_VERSION;
    }

    public function gingerGetPluginName()
    {
        return WC_Ginger_BankConfig::PLUGIN_NAME;
    }
    public function gingerGetPlatformName()
    {
        return 'WooCommerce';
    }

    public function gingerGetPlatformVersion()
    {
        return get_option('woocommerce_version');
    }

    /**
     * Method returns returns WC_Api callback URL
     *
     * @return string
     */
    public function gingerGetReturnUrl()
    {
        return add_query_arg('wc-api', 'woocommerce_ginger', home_url('/'));
    }

    /**
     * Method formats the floating point amount to amount in cents
     *
     * @param float $total
     * @return int
     */
    public function gingerGetAmountInCents($total)
    {
        return (int) round($total * 100);
    }

    /**
     * Method returns order total in cents based on current WooCommerce version.
     * @return int
     */
    public function gingerGetAmount()
    {
        if (version_compare(get_option('woocommerce_version'), '3.0', '>=')) {
            $orderTotal = $this->woocommerceOrder->get_total();
        } else {
            $orderTotal = $this->woocommerceOrder->order->order_total;
        }

        return $this->gingerGetAmountInCents($orderTotal);
    }

    /**
     * Method returns currencyCurrency in ISO-4217 format
     *
     * @return string
     */
    public function gingerGetCurrency()
    {
        return get_woocommerce_currency();
    }

    /**
     * Method returns customer information from the order
     * @return array
     */
    public function gingerGetCustomerInfo()
    {
        $this->billingAddress = $this->woocommerceOrder->get_address('billing');
        $this->shippingAddress = $this->woocommerceOrder->get_address('shipping');

        if (!$this->shippingAddress['address_1'] && !$this->shippingAddress['address_2'])
        {
            $this->shippingAddress = $this->billingAddress;
        }

        return array_filter([
            'address_type' => $this->gingerGetAddressType(),
            'merchant_customer_id' => $this->gingerGetMerchantCustomerID(),
            'email_address' => $this->gingerGetEmailAddress(),
            'first_name' => $this->gingerGetFirstName(),
            'last_name' => $this->gingerGetLastName(),
            'address' => $this->gingerGetAddress(),
            'postal_code' => $this->gingerGetPostalCode(),
            'country' => $this->gingerGetCountry(),
            'phone_numbers' => $this->gingerGetPhoneNumbers(),
            'user_agent' => $this->gingerGetUserAgent(),
            'ip_address' => $this->gingerGetIPAddress(),
            'locale' => $this->gingerGetLocale(),
            'gender' => $this->gingerGetGender(),
            'birthdate' => $this->gingerGetBirthdate(),
            'additional_addresses' => $this->gingerGetAdditionalAddresses()
        ]);
    }

    /**
     * Function returns value from gender field
     * @return string|null
     */
    public function gingerGetGender()
    {
        return WC_Ginger_Helper::gingerGetCustomPaymentField('gender');
    }

    /**
     * Function returns values from birthday fields
     * @return string
     */
    public function gingerGetBirthdate():string
    {
        $birthdate = implode('-', [
            WC_Ginger_Helper::gingerGetCustomPaymentField('ginger_afterpay_date_of_birth_year'),
            WC_Ginger_Helper::gingerGetCustomPaymentField('ginger_afterpay_date_of_birth_month'),
            WC_Ginger_Helper::gingerGetCustomPaymentField('ginger_afterpay_date_of_birth_day')
        ]);

        // removing it will make sure it gets removed if empty and thus not validated
        if ($birthdate == '--') $birthdate = '';
        return $birthdate;
    }

    /**
     * Function returns additional addresses
     * @return string[][]
     */
    public function gingerGetAdditionalAddresses():array
    {
        return [
            [
                'address_type' => 'billing',
                'address' => (string) trim($this->billingAddress['address_1'])
                    .' '.trim($this->billingAddress['address_2'])
                    .' '.trim(str_replace(' ', '', $this->billingAddress['postcode']))
                    .' '.trim($this->billingAddress['city']),
                'country' => (string) $this->billingAddress['country'],
            ]
        ];
    }

    /**
     * Function returns customer address
     * @return string
     */
    public function gingerGetAddress():string
    {
        return trim($this->shippingAddress['address_1'])
            .' '.trim($this->shippingAddress['address_2'])
            .' '.trim(str_replace(' ', '', $this->shippingAddress['postcode']))
            .' '.trim($this->shippingAddress['city']);
    }

    /**
     * Function returns customer ip
     * @return string
     */
    public function gingerGetIPAddress():string
    {
        return version_compare(get_option('woocommerce_version'), '3.0', '>=')
            ? $this->woocommerceOrder->get_customer_ip_address()
            : $this->woocommerceOrder->customer_ip_address;
    }

    /**
     * Function returns customer user agent
     * @return string
     */
    public function gingerGetUserAgent():string
    {
        return version_compare(get_option('woocommerce_version'), '3.0', '>=')
            ? $this->woocommerceOrder->get_customer_user_agent()
            : $this->woocommerceOrder->customer_user_agent;
    }

    /**
     * Function returns locale
     * @return string
     */
    public function gingerGetLocale():string
    {
        return get_locale();
    }

    /**
     * Functions return customer phone numbers
     * @return array
     */
    public function gingerGetPhoneNumbers(): array
    {
        return [
            $this->billingAddress['phone']
        ];
    }

    /**
     * Function returns customer country
     * @return string
     */
    public function gingerGetCountry():string
    {
        return $this->shippingAddress['country'];
    }

    /**
     * Function returns address's post code
     * @return string
     */
    public function gingerGetPostalCode():string
    {
        return str_replace(' ', '', $this->shippingAddress['postcode']);
    }

    /**
     * Function returns customer first name
     * @return string
     */
    public function gingerGetFirstName():string
    {
        return $this->shippingAddress['first_name'];
    }

    /**
     * Function returns customer last name
     * @return string
     */
    public function gingerGetLastName():string
    {
        return $this->shippingAddress['last_name'];
    }

    /**
     * Function returns customer email address
     * @return string
     */
    public function gingerGetEmailAddress():string
    {
        return $this->billingAddress['email'];
    }

    /**
     * Function returns merchant customer ID
     * @return string
     */
    public function gingerGetMerchantCustomerID():string
    {
        return $this->woocommerceOrder->get_user_id();
    }

    /**
     * Function returns merchant order ID
     * @return string
     */
    public function gingerGetMerchantOrderID():string
    {
        return $this->merchant_order_id;
    }

    /**
     * Function returns address type
     * @return string
     */
    public function gingerGetAddressType(): string
    {
        return 'customer';
    }

    /**
     * Get product price based on WooCommerce version.
     *
     * @param WC_Product $product
     * @return float|string
     */
    public function gingerGetProductPrice($orderLine, $order)
    {
        if (version_compare(get_option('woocommerce_version'), '3.0', '>=')) {
            return $order->get_item_total( $orderLine, true );
        } else {
            $product = $orderLine->get_product();
            return $product->get_price_including_tax();
        }
    }

    /**
     * Function returns order lines
     * @param $order
     * @return array
     */
    public function gingerGetOrderLines($order)
    {
        $orderLines = [];
        $productIds = [];

        foreach ($order->get_items() as $orderLine)
        {
            $productId = (int) $orderLine->get_variation_id() ?: $orderLine->get_product_id();
            $productIds[] = $productId;

            $imageURL = wp_get_attachment_url($orderLine->get_product()->get_image_id());
            $orderLines[] = array_filter([
                'url' => get_permalink($productId),
                'name' => $orderLine->get_name(),
                'type' => 'physical',
                'amount' => $this->gingerGetAmountInCents($this->gingerGetProductPrice($orderLine, $order)),
                'currency' => $this->gingerGetCurrency(),
                'quantity' => (int) $orderLine->get_quantity(),
                'image_url' => $imageURL ? $imageURL : null,
                'vat_percentage' => $this->gingerGetAmountInCents($this->gingerGetProductTaxRate($orderLine->get_product())),
                'merchant_order_line_id' => (string) $productId
            ],
                function($value) {
                    return ! is_null($value);
                });
        }

        if ($order->get_total_shipping() > 0) {
            $orderLines[] = $this->gingerGetShippingOrderLine($order);
        }


        //bug-fix: PLUG-1381
        if (count($productIds) !== count(array_unique($productIds))) {
            $orderLines = $this->gingerGetUniqueOrderLines($orderLines);
        }

        return $orderLines;
    }

    /**
     * @param $orderLines - array that contains order line duplications
     * @return array - array without duplications
     */
    public function gingerGetUniqueOrderLines($orderLines)
    {
        $updatedOrderLines = [];

        foreach ($orderLines as $orderLine) {

            $addOrderLine = true;

            foreach ($updatedOrderLines as $key => $updatedOrderLine) {
                if ($updatedOrderLine['merchant_order_line_id'] == $orderLine['merchant_order_line_id']) {
                    $updatedOrderLines[$key]['quantity']+= $orderLine['quantity'];
                    $addOrderLine = false; //order line already exists, so we just sum the quantity
                    break;
                }
            }

            if ($addOrderLine) {
                $updatedOrderLines[] = $orderLine;
            }

        }

        return $updatedOrderLines;
    }

    /**
     * Since single item in the cart can have multiple taxes,
     * we need to sum those taxes up.
     *
     * @param $product
     * @return int
     */
    public function gingerGetProductTaxRate(WC_Product $product)
    {
        $WC_Tax = new WC_Tax();
        $totalTaxRate = 0;
        foreach ($WC_Tax->get_rates($product->get_tax_class()) as $taxRate) {
            $totalTaxRate += $taxRate['rate'];
        }
        return $totalTaxRate;
    }

    /**
     * Function returns shipping order line
     * @param $order
     * @return array
     */
    public function gingerGetShippingOrderLine($order)
    {
        return [
            'name' => $order->get_shipping_method(),
            'type' => 'shipping_fee',
            'amount' => $this->gingerGetAmountInCents($order->get_shipping_total() + $order->get_shipping_tax()),
            'currency' => $this->gingerGetCurrency(),
            'vat_percentage' => $this->gingerGetAmountInCents($this->gingerGetShippingTaxRate()),
            'quantity' => 1,
            'merchant_order_line_id' => (string) (count($order->get_items()) + 1)
        ];
    }

    /**
     * Since shipping fees can have multiple taxes applied,
     * we need to sum those taxes up.
     *
     * @return int
     */
    public function gingerGetShippingTaxRate()
    {
        $totalTaxRate = 0;
        foreach (WC_Tax::get_shipping_tax_rates() as $taxRate) {
            $totalTaxRate += $taxRate['rate'];
        }
        return $totalTaxRate;
    }

    /**
     * Generate order description
     * @return string
     */
    public function gingerGetOrderDescription()
    {
        return sprintf(__('Your order %s at %s', WC_Ginger_BankConfig::BANK_PREFIX), $this->merchant_order_id, get_bloginfo('name'));
    }

    /**
     * Function returns webhook URL
     * @param WC_Payment_Gateway $gateway
     * @return null|string
     */
    public function gingerGetWebhookUrl()
    {
        return $this->gingerGetReturnUrl();
    }

    /**
     * Function set the merchant order ID
     * @param $merchantOrderID
     */
    public function gingerSetMerchantOrderID($merchantOrderID)
    {
        $this->merchant_order_id = $merchantOrderID;
    }

}