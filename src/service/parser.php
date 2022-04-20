<?php

declare(strict_types=1);

namespace CommissionFees\Service;

class Parser extends Logs
{
    public const CSV_COLUMNS = ['date', 'user_id', 'user_type', 'op_type', 'amount', 'currency'];

    /**
     * Full path ofa csv file.
     *
     * @var string
     */
    private $csv_file;

    /**
     * Array of transactions.
     *
     * @var array
     */
    private $csv_rows = [];

    /**
     * Array of calculated fees.
     *
     * @var array
     */
    private $fees = [];

    /**
     * Array of calculated fees.
     *
     * @var array
     */
    private $options = [];

    /**
     * Constructor, Check if a csv file exist.
     *
     * @param string $csv_file full path of a csv file
     *
     * @throws Exception When file is not exist
     */
    public function __construct(array $options)
    {
        $this->options = $options;
        if (!isset($this->options['csv_file']) && !file_exists($this->options['csv_file'])) {
            throw new \Exception('Csv file doesn\'t exist.');
        }

        if (!isset($this->options['currency_base_precision'])) {
            $this->options['currency_base_precision'] = 2;
        }

        $this->csv_file = $this->options['csv_file'];
        if (isset($this->options['debug_mode']) && $this->options['debug_mode']) {
            parent::setDebugMode(true);
        }
    }

    /**
     * Parse a csv file and calculate the fees of each line.
     *
     * @return array Empty on error, an associative array contains the fees of each transaction along with its details
     */
    public function parseFile(): array
    {
        try {
            $stream = new \SplFileObject($this->csv_file);
            $stream->setFlags(\SplFileObject::DROP_NEW_LINE | \SplFileObject::SKIP_EMPTY | \SplFileObject::READ_CSV);

            // Get rates
            $options[] = isset($this->options['rates_api_url']) ? $options['rates_api_url'] = $this->options['rates_api_url'] : null;
            $options[] = isset($this->options['currency_precision']) ? $options['currency_precision'] = $this->options['currency_precision'] : null;
            $rates = new Rates($options);

            // Parse the file
            foreach ($stream as $line) {
                // Set header for the parsed lines
                if (self::arrayFIllValuesKeys(Parser::CSV_COLUMNS, $line) === false) {
                    $this->rows = [];
                    parent::setErrorMessage(sprintf('Current line couldn\'t parsed.\r\nLine: %s' . $line));
                    break;
                }

                // Set user id as key for his transactions
                if (!isset($this->rows[$line['user_id']])) {
                    $this->rows[$line['user_id']]['user_type'] = $line['user_type'];
                    $this->rows[$line['user_id']]['transactions'] = ['deposit' => [], 'withdraw' => []];
                }

                // Save deposit and withdraw transactions separately
                $fee = 0.0;
                $precision = $rates->getCurrencyPrecision($line['currency']);
                $exchange_rate = ($line['currency'] === 'EUR') ? 1 : $rates->getRateOf($line['currency'], $precision);
                $amount_rate = $line['amount'] / $exchange_rate;
                $amount_rate = $rates::getNumberPrecision((float) $amount_rate, $precision);
                // Get fee precision
                $fee_precision = (isset($this->options['currency_fee_precision'])
                    && isset($this->options['currency_fee_precision'][$line['currency']])) ? $this->options['currency_fee_precision'][$line['currency']] : 2;
                if ($line['op_type'] === 'deposit') {
                    $fee = Deposit::calcFee($line['user_type'], (float) Withdraw::WEEKLY_LIMIT, (float) $amount_rate);
                    $fee = sprintf('%01.2f', Deposit::roundUp($fee * $exchange_rate, $fee_precision));
                    $this->rows[$line['user_id']]['transactions']['deposit'][] = ['date' => $line['date'], 'amount' => $line['amount'],
                                                                                            'currency' => $line['currency'], 'fees' => $fee, ];
                    $this->fees[] = $fee;
                } else {
                    // This is the first transaction of user
                    if (empty($this->rows[$line['user_id']]['transactions']['withdraw'])) {
                        $fee = Withdraw::calcFee($line['user_type'], (float) Withdraw::WEEKLY_LIMIT, (float) $amount_rate);
                        $fee = sprintf('%01.2f', Withdraw::roundUp($fee * $exchange_rate, $fee_precision));
                        $this->rows[$line['user_id']]['transactions']['withdraw'][] = ['total' => (Withdraw::WEEKLY_OPERATIONS_LIMIT - 1), 'rest_discount' => Withdraw::WEEKLY_LIMIT - $amount_rate,
                                                                                            ['date' => $line['date'], 'amount' => $line['amount'], 'currency' => $line['currency'], 'fees' => $fee], ];

                        $this->fees[] = $fee;
                    } else {
                        $pop_week = array_pop($this->rows[$line['user_id']]['transactions']['withdraw']);
                        $pop_day = array_pop($pop_week);
                        $new_day = [];
                        if (self::isDateInSameWeek($pop_day['date'], $line['date'])) {
                            if ($pop_week['total'] > 0) {
                                $fee = Withdraw::calcFee($line['user_type'], (float) $pop_week['rest_discount'], (float) $amount_rate);
                                $fee = sprintf('%01.2f', Withdraw::roundUp($fee * $exchange_rate, $fee_precision));
                                $pop_week['rest_discount'] = ($pop_week['rest_discount'] > 0) ? $pop_week['rest_discount'] - $line['amount'] : 0;
                                $pop_week['total'] = ($pop_week['rest_discount'] > 0) ? --$pop_week['total'] : $pop_week['total'];
                            } else {
                                $fee = Withdraw::calcFee($line['user_type'], 0, (float) $amount_rate);
                                $fee = sprintf('%01.2f', Withdraw::roundUp($fee * $exchange_rate, $fee_precision));
                            }

                            $new_day = ['date' => $line['date'], 'amount' => $line['amount'], 'currency' => $line['currency'], 'fees' => $fee];
                            $this->fees[] = $fee;
                            // Push it back
                            $pop_week[] = $pop_day;
                            $pop_week[] = $new_day;
                            $this->rows[$line['user_id']]['transactions']['withdraw'][] = $pop_week;
                            unset($pop_week);
                            unset($pop_day);
                            unset($new_day);
                        }
                        // This is a new week
                        else {
                            // Put back any previous data
                            if (isset($pop_day)) {
                                $pop_week[] = $pop_day;
                                $this->rows[$line['user_id']]['transactions']['withdraw'][] = $pop_week;

                                unset($pop_week);
                                unset($pop_day);
                            }

                            $fee = Withdraw::calcFee($line['user_type'], (float) Withdraw::WEEKLY_LIMIT, (float) $amount_rate);
                            $fee = sprintf('%01.2f', Withdraw::roundUp($fee * $exchange_rate, $fee_precision));
                            $this->rows[$line['user_id']]['transactions']['withdraw'][] = ['total' => (Withdraw::WEEKLY_OPERATIONS_LIMIT - 1), 'rest_discount' => Withdraw::WEEKLY_LIMIT - $line['amount'],
                                                                                                ['date' => $line['date'], 'amount' => $line['amount'], 'currency' => $line['currency'], 'fees' => $fee], ];
                            $this->fees[] = $fee;
                        }
                    }
                }
            }
        } catch (Exception $ex) {
            $this->rows = [];
            parent::setErrorMessage(sprintf('An error occurred during parsing the csv file, more information about it here: %s', $ex->getMessage()));
        }

        return $this->rows;
    }

    /**
     * Get the JSON structure of the CSV transactions.
     *
     * @return string JSON string on success, empty on error
     */
    public function generateJson(): string
    {
        $str_json = '';
        if (empty($this->csv_rows)) {
            $str_json = '';
        }

        $str_json = json_encode($this->csv_rows);

        return $str_json;
    }

    /**
     * Generate a file of transactions fees.
     *
     * @return string Name of file that holding fees
     */
    public function generateFeesFile(string $output_file = ''): string
    {
        $file_name = 'fees-' . date('Y-m-d') . '.txt';
        if (!empty($output_file)) {
            $file_name = $output_file;
        }

        $data = implode(PHP_EOL, $this->fees);
        file_put_contents($file_name, $data);

        return $file_name;
    }

    /**
     * Get transactions fees.
     *
     * @return array An array contains a list of fees
     */
    public function getFees(): array
    {
        return $this->fees;
    }

    /**
     * Check if the $date2 is in the same week of $date1.
     *
     * @param string $date1 Full date in Y-m-d format
     * @param string $date2 Full date in Y-m-d format
     *
     * @return bool true if $date2 is in the same week of $date1, false else
     */
    public static function isDateInSameWeek(string $date1, string $date2): bool
    {
        $begin = date('l', strtotime($date1)) === 'Monday' ? $date1 : $date1 . ' last Monday';
        $begin = (new \DateTime($begin))->format('Y-m-d');
        // $end = (new \DateTime($date1 . ' next Saturday'))->format('Y-m-d');
        $end = (new \DateTime($date1 . ' next Monday'))->format('Y-m-d');
        if ($date2 >= $begin && $date2 < $end) {
            return true;
        }

        return false;
    }

    /**
     * Set keys to an array of just values.
     *
     * @param array $keys   array of keys to set for the values
     * @param array $values array to set keys to it
     *
     * @return bool True success, false on failure
     */
    public static function arrayFIllValuesKeys(array $keys, array &$values): bool
    {
        if (is_array($keys) && is_array($values)
            && count($keys) === count($values)) {
            $i = 0;
            foreach ($keys as $key) {
                $values[$key] = $values[$i];
                unset($values[$i++]);
            }

            return true;
        }

        return false;
    }
}
