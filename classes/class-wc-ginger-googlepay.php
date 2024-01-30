<?php

if (!defined('ABSPATH')) {
    exit;
}

class WC_Ginger_GooglePay extends WC_Ginger_BankGateway
{
    public function __construct()
    {
        $this->id = WC_Ginger_BankConfig::BANK_PREFIX.'_google-pay';
        $this->icon = false;
        $this->has_fields = false;
        $this->method_title = __('Google Pay - '.WC_Ginger_BankConfig::BANK_LABEL, WC_Ginger_BankConfig::BANK_PREFIX);
        $this->method_description = __('Google Pay - '.WC_Ginger_BankConfig::BANK_LABEL, WC_Ginger_BankConfig::BANK_PREFIX);

        parent::__construct();
    }
}
