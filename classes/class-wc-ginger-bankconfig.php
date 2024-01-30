<?php
if (!defined('ABSPATH')) {
    exit;
}

class WC_Ginger_BankConfig
{

    /**
     * GINGER_ENDPOINT used for create Ginger client
     */
    const GINGER_BANK_ENDPOINT = 'https://api.gateway.xpate.com';

    /**
     * BANK_PREFIX and BANK_LABEL used to provide GPE solution
     */
    const BANK_PREFIX = "xpate";
    const BANK_LABEL = "Xpate";
    const PLUGIN_NAME = "xpate-online-woocommerce";

    /**
     * EMS Online supported payment methods
     */
    public static $BANK_PAYMENT_METHODS = [
        'xpate_ideal',
        'xpate_bank-transfer',
        'xpate_credit-card',
        'xpate_bancontact',
        'xpate_klarna-pay-now',
        'xpate_paypal',
        'xpate_klarna-pay-later',
        'xpate_payconiq',
        'xpate_afterpay',
        'xpate_apple-pay',
        'xpate_pay-now',
        'xpate_amex',
        'xpate_viacash',
        'xpate_klarna-direct-debit',
        'xpate_google-pay',
        'xpate_sofort',
        'xpate_giropay',
        'xpate_swish',
        'xpate_mobilepay',
    ];

    /**
     * EMS Online payment methods classnames
     */
    public static $WC_BANK_PAYMENT_METHODS = [
        'WC_Ginger_Callback',
        'WC_Ginger_Ideal',
        'WC_Ginger_Banktransfer',
        'WC_Ginger_Bancontact',
        'WC_Ginger_Creditcard',
        'WC_Ginger_PayPal',
        'WC_Ginger_KlarnaPayLater',
        'WC_Ginger_KlarnaPayNow',
        'WC_Ginger_Payconiq',
        'WC_Ginger_AfterPay',
        'WC_Ginger_ApplePay',
        'WC_Ginger_PayNow',
        'WC_Ginger_Amex',
        'WC_Ginger_ViaCash',
        'WC_Ginger_Sofort',
        'WC_Ginger_GooglePay',
        'WC_Ginger_KlarnaDirectDebit',
        'WC_Ginger_GiroPay',
        'WC_Ginger_Swish',
        'WC_Ginger_MobilePay',
    ];
}