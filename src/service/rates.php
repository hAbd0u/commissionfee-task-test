<?php

declare(strict_types=1);

namespace CommissionFees\Service;

date_default_timezone_set('UTC');

class Rates
{
    private $error_description = '';
    private $api_end_point = 'https://developers.paysera.com/tasks/api/currency-exchange-rates';
    private $last_check_time = 0;
    private $cache_time = 600; // 10 Minutes

    /**
     * Set the API end point to get rates.
     *
     * @param string $api_end_point The API url to get rates (optional)
     */
    public function __construct(string $api_end_point = '')
    {
        if (!empty($api_end_point)) {
            $this->api_end_point = $api_end_point;
        }
    }

    /**
     * Get the rate of currency based on EUR.
     *
     * @param string $currency Currency symbol
     *
     * @return float The rate of currency on success, 0 or -1 on error
     */
    public function getRateOf(string $currency): float
    {
        $rates = self::getRates();
        if (empty($rates)) {
            return -1;
        }

        if (isset($rates[$currency])) {
            return $rates[$currency];
        }

        $this->error_description = 'Currency symbol couldn\'t be found.';
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
            $this->error_description = 'End point API couldn\'t be fetched.';
            // return 0;
            return [];
        }

        $this->rates = json_decode($this->rates, true);
        if ($this->rates === null) {
            $this->error_description = 'Invalid JSON format';
            // return -1;
            return [];
        }

        if (isset($this->rates['rates'])) {
            return $this->rates['rates'];
        }

        $this->error_description = 'Unknown error, may be the format is changed.';
        // return -2;
        return [];
    }

    /**
     * Get the text description of the last error.
     *
     * @return string A description for last error occurred
     */
    public function getLastError()
    {
        return $this->error_description;
    }
}
