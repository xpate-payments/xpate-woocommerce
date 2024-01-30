<?php

if (!defined('ABSPATH')) {
    exit;
}

class WC_Ginger_Creditcard extends WC_Ginger_BankGateway
{
    public function __construct()
    {
        $this->id = WC_Ginger_BankConfig::BANK_PREFIX.'_credit-card';
        $this->icon = false;
        $this->has_fields = false;
        $this->method_title = __('Mastercard, VISA, Maestro or V PAY - '.WC_Ginger_BankConfig::BANK_LABEL, WC_Ginger_BankConfig::BANK_PREFIX);
        $this->method_description = __('Mastercard, VISA, Maestro or V PAY - '.WC_Ginger_BankConfig::BANK_LABEL, WC_Ginger_BankConfig::BANK_PREFIX);

        parent::__construct();
    }
}
