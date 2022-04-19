<?php

declare(strict_types=1);

namespace CommissionFees\Tests\Service;

use PHPUnit\Framework\TestCase;
use CommissionFees\Service\Withdraw;

class WithdrawTest extends TestCase
{

    public function setUp(): void
    {
    }

    /**
     * @dataProvider    withdrawFeesDataProvider
     */
    public function testCalcFee(string $account_type, float $discount_fee, float $amount, float $expected_result) {
        $this->assertEquals($expected_result, Withdraw::calcFee($account_type, $discount_fee, $amount));
    }

    /**
     * 
     */
    public function withdrawFeesDataProvider(): array 
    {
        return [
            ['private', 1000, 200.00, 0],
            ['private', 1000, 1000.00, 0],
            ['private', 1000, 1400.00, 1.2],
            ['business', 1000, 1500, 7.5],
            ['private', 500, 800, 0.9],
            ['business', 0, 97450, 487.25],
        ];
    }
}
