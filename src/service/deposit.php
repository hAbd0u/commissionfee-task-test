<?php

declare(strict_types=1);

namespace CommissionFees\Service;

require_once __DIR__.'/../../vendor/autoload.php';

class Deposit extends Operation
{
    public const DEPOSIT_FEE_CLIENT = 0.03 / 100;
    public const DEPOSIT_FEE_BUSINESS = 0.03 / 100;

    public function __construct()
    {
    }

    /**
     * Calculate the fees of an amount.
     *
     * @return float The final fees
     */
    public static function calcFee(string $account_type, float $discount_fee, float $amount): float
    {
        $fee_percent = ($account_type === 'private') ? self::DEPOSIT_FEE_CLIENT : self::DEPOSIT_FEE_BUSINESS;

        return $amount * $fee_percent;
    }

    /**
     * Calculate the final amount.
     *
     * @return float The final amount
     */
    public static function getFinalAmount(int $account_type, float $discount_fee, float $amount): float
    {
        return $amount - self::calcFee($account_type, $discount_fee, $amount);
    }
}
