<?php

declare(strict_types=1);

namespace CommissionFees\Tests\Service;

use PHPUnit\Framework\TestCase;
use CommissionFees\Service\Rates;

class RatesTest extends TestCase
{
    private $rates;
    public function setUp()
    {
        $this->rates = new Rates();
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
            'USD' => ['USD', 1.129031],
            'JPY' => ['JPY', 130.869977],
            'GBP' => ['GBP', 0.835342],
        ];
    }
}
