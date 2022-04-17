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
     *
     * @dataProvider    ratesDataProvider
     */
    public function testGetRateOfCurrency(string $currency, float $expected_rate): void
    {
        $this->assertEquals(
            $expected_rate,
            $this->rates->getRateOf($currency)
        );
    }


    public function testCurrencyExist(): void
    {
        $this->assertEquals(0, $this->rates->getRateOf('sss'));
        $this->assertEquals('Currency symbol couldn\'t be found.', $this->rates->getLastError());
    }


    /**
     * Change this according to today rate before making any test.
     */
    public function ratesDataProvider(): array 
    {
        return [
            [200.00, 0.06],
            [1000.00, 3.0],
            [800, 0.24],
        ];
    }
}
