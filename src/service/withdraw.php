<?php

declare(strict_types=1);

namespace CommissionFees\Service;

require_once __DIR__.'/../../vendor/autoload.php';

class Withdraw extends Operation
{
    public const WEEKLY_LIMIT = 1000;
    public const WEEKLY_OPERATIONS_LIMIT = 3;

    public const WITHDRAW_FEE_CLIENT = 0.3 / 100;
    public const WITHDRAW_FEE_BUSINESS = 0.5 / 100;

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
        $fee_amount = $amount;
        $fee_percent = self::WITHDRAW_FEE_BUSINESS;
        if ($account_type === 'private') {
            $fee_amount = ($discount_fee > 0) ? $amount - $discount_fee : $amount;
            if ($fee_amount <= 0) {
                return 0;
            }

            $fee_percent = self::WITHDRAW_FEE_CLIENT;
        }

        $fee_amount = $fee_amount * $fee_percent;

        return $fee_amount;
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
