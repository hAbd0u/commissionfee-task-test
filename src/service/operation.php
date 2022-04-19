<?php

declare(strict_types=1);

namespace CommissionFees\Service;

abstract class Operation extends Logs
{
    /**
     * Roup up a float number.
     *
     * @float $amount
     * @int $precision
     *
     * @return float The rounded number
     */
    public static function roundUp(float $amount, int $precision = 2): float
    {
        if ($precision < 0) {
            $precision = 0;
        }

        // return sprintf("%01.2f", ceil(($p = pow(10, $precision)) * $amount) / $p);
        return ceil(($p = pow(10, $precision)) * $amount) / $p;
    }

    /**
     * Calculate the fees of an amount.
     *
     * @return float The final fees
     */
    abstract public static function calcFee(string $account_type, float $discount_fee, float $amount): float;

    /**
     * Calculate the final amount.
     *
     * @return float The final amount
     */
    abstract public static function getFinalAmount(int $account_type, float $discount_fee, float $amount): float;
}
