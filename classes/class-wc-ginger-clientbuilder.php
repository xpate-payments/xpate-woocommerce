<?php

if (!defined('ABSPATH')) {
    exit;
}

class WC_Ginger_Clientbuilder
{

    /**
     * Function ginger_get_client
     *
     * @param string $paymentMethod
     * @return \Ginger\ApiClient
     */
    public static function gingerBuildClient($paymentMethod = "")
    {
        $settings = get_option('woocommerce_ginger_settings');
        if (!is_array($settings)) return false;
        $apiKey = $settings['api_key'] ?? false;
        if($paymentMethod) $apiKey = self::gingerGetTestAPIKey($paymentMethod) ?: $apiKey;
        if (!$apiKey) return false;

        try {
            $client = \Ginger\Ginger::createClient(
                WC_Ginger_BankConfig::GINGER_BANK_ENDPOINT,
                $apiKey,
                ($settings['bundle_cacert'] == 'yes') ?
                    [
                        CURLOPT_CAINFO => self::gingerGetCaCertPath()
                    ] : []
            );
        } catch (Exception $exception) {
            WC_Admin_Notices::add_custom_notice(WC_Ginger_BankConfig::BANK_PREFIX.'-error', $exception->getMessage());
        }

        return $client;
    }

    /**
     * Function get test-api-key from gateway settings
     * @param $paymentMethod - gateway's id
     * @return mixed
     */
    public static function gingerGetTestAPIKey($paymentMethod)
    {
        $settings = get_option('woocommerce_'.$paymentMethod.'_settings');
        if (!is_array($settings)) return false;
        if (!array_key_exists('test_api_key', $settings)) return false;
        return $settings['test_api_key'];
    }

    /**
     * Get CA certificate path
     *
     * @return bool|string
     */
    public static function gingerGetCaCertPath()
    {
        return realpath(plugin_dir_path(__FILE__).'../assets/cacert.pem');
    }


}