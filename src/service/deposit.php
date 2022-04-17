<?php

declare(strict_types=1);

namespace CommissionFees\Service;

const DEPOSIT_FEE_CLIENT = 0.03 / 100;
const DEPOSIT_FEE_BUSINESS = 0.03 / 100;

class Deposit
{
    public function __construct()
    {
    }

    /**
     * Calculate the fees of an amount
     * 
     * @return float    The final fees 
     */
    public static function getDepositFee(float $amount): float
    {
        return ($amount * DEPOSIT_FEE_CLIENT);
    }

    /**
     * Calculate the final amount
     * 
     * @return float    The final amount 
     */
    public static function getFinalAMount(float $amount): float
    {
        return ($amount - self::getDepositFee($amount));
    }



}
