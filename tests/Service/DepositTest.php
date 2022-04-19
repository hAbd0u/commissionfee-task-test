<?php

declare(strict_types=1);

namespace CommissionFees\Tests\Service;

use PHPUnit\Framework\TestCase;
use CommissionFees\Service\Deposit;

class DepositTest extends TestCase
{

    public function setUp(): void
    {
    }

    /**
     * @dataProvider    depositFeesDataProvider
     */
    public function testCalcFee(string $account_type, float $discount_fee, float $amount, float $expected_result) {
        $this->assertEquals($expected_result, Deposit::calcFee($account_type, $discount_fee, $amount));
    }

    /**
     * 
     */
    public function depositFeesDataProvider(): array 
    {
        return [
            ['private', 0, 200.00, 0.06],
            ['private', 0, 1000.00, 0.3],
            ['business', 0, 1500, 0.45],
            ['private', 0, 800, 0.24],
            ['business', 0, 97430, 29.229],
        ];
    }
}
