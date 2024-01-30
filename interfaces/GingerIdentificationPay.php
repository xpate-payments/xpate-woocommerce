<?php

/**
 * For payment methods that have identification stage (ex. BankTransfer)
 * Interface GingerIdentificationPay
 */
interface GingerIdentificationPay
{
    public function gingerIdentificationProcess($order);
}