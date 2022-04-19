<?php

declare(strict_types=1);

namespace CommissionFees\Service;

require_once __DIR__.'/../../vendor/autoload.php';

date_default_timezone_set('UTC');

class Rates extends Logs
{
    private $last_check_time = 0;
    private $api_end_point = 'https://developers.paysera.com/tasks/api/currency-exchange-rates';

    // 10 Minutes
    private $cache_time = 600;

    /**
     * @var array
     */
    private static $currencies = [];

    /**
     * Set the API end point to get rates.
     *
     * @param array $api_end_point The API url to get rates (optional)
     */
    public function __construct(array $options = [])
    {
        if (isset($options['rates_api_url'])) {
            $this->api_end_point = $options['rates_api_url'];
        }

        if (isset($options['currency_precision'])) {
            self::$currencies = $options['currency_precision'];
        }
    }

    /**
     * Get the rate of currency based on EUR.
     *
     * @param string $currency Currency symbol
     *
     * @return float The rate of currency on success, 0 or -1 on error
     */
    public function getRateOf(string $currency, int $precision = 4): float
    {
        $rates = self::getRates();
        if (empty($rates)) {
            return -1;
        }

        if (isset($rates[$currency])) {
            return self::getNumberPrecision($rates[$currency], $precision);
        }

        parent::printDebugMessage('Currency symbol couldn\'t be found.');

        return 0;
    }

    /**
     * Get the rates based on EUR.
     *
     * @return array array of rates on success, empty on error
     */
    public function getRates(): array
    {
        if ($this->last_check_time === 0 || ($this->last_check_time + $this->cache_time) < time()) {
            $this->rates = file_get_contents($this->api_end_point);
        }

        if ($this->rates === false) {
            parent::printDebugMessage('End point API couldn\'t be fetched.');
            // return 0;
            return [];
        }

        $this->rates = json_decode($this->rates, true);
        if ($this->rates === null) {
            parent::printDebugMessage('Invalid JSON format.');
            // return -1;
            return [];
        }

        if (isset($this->rates['rates'])) {
            return $this->rates['rates'];
        }

        parent::printDebugMessage('Unknown error, may be the format is changed.');
        // return -2;
        return [];
    }

    /**
     * Get the rate of currency based on EUR.
     *
     * @param string $currency Currency symbol
     *
     * @return float The rate of currency on success, 0 or -1 on error
     */
    public static function getNumberPrecision(float $amount, int $precision): float
    {
        $num = ($amount * ($p = pow(10, $precision))) / $p;

        return $num;
    }

    /**
     * Get the precision of currency.
     *
     * @param string $currency Currency symbol
     *
     * @return int The precision of currency on success, or a default value if it is doesn't exist
     */
    public static function getCurrencyPrecision(string $currency): int
    {
        $currencies = self::$currencies;
        if (empty(self::$currencies)) {
            $currencies = [
                'EUR' => 2,
                'USD' => 4,
                'JPY' => 2,
            ];
        }

        if (isset($currencies[$currency])) {
            return $currencies[$currency];
        }

        return 4;
    }
}
