<?php

declare(strict_types=1);

namespace CommissionFees\Service;

const CSV_COLUMNS = ['date', 'user_id', 'user_type', 'op_type', 'amount', 'currency'];

class Parser extends Logs
{
    private $csv_file;
    private $csv_rows = [];

    /**
     * Constructor, Check if a csv file exist.
     *
     * @param string $csv_file full path of a csv file
     *
     * @throws Exception When file is not exist
     */
    public function __construct(string $csv_file)
    {
        if (!file_exists($csv_file)) {
            throw new \Exception('Csv file doesn\'t exist.');
        }

        $this->csv_file = $csv_file;
        parent::setDebugMode(true);
    }

    /**
     * Parse a csv file and calculate the fees of each line.
     *
     * @return array Empty on error, an associative array contains the fees of each transaction along with its details
     */
    public function parseFile(): array
    {
        $this->rows = [];
        try {
            $stream = new \SplFileObject($this->csv_file);
            $stream->setFlags(\SplFileObject::DROP_NEW_LINE | \SplFileObject::SKIP_EMPTY | \SplFileObject::READ_CSV);

            foreach ($stream as $line) {
                if (self::arrayFIllValuesKeys(CSV_COLUMNS, $line) === false) {
                    $this->rows = [];
                    parent::setErrorMessage(sprintf('Current line couldn\'t parsed as CSV.\r\nLine: %s'.$line));
                    break;
                }

                $week = self::weekOfDay($line['date']);
                if ($week < 1) {
                    parent::printDebugMessage(sprintf('Skipping a line, info: %s', json_encode($line)));
                    continue;
                }

                // Set Year-Month as key for number of transactions
                $year_month = explode('-', $line['date']);
                $year_month = $year_month[0].'-'.$year_month[1];

                // Set user id as key for his transactions
                if (!isset($this->rows[$line['user_id']])) {
                    $this->rows[$line['user_id']]['user_type'] = $line['user_type'];
                }

                if (!isset($this->rows[$line['user_id']][$year_month])) {
                    $this->rows[$line['user_id']][$year_month] = [];
                }

                // Register each transaction details in array like structure
                $total = 0;
                if (!isset($this->rows[$line['user_id']][$year_month][$week])) {
                    $this->rows[$line['user_id']][$year_month][$week]['total'] = 1;
                    $this->rows[$line['user_id']][$year_month][$week]['transactions'][] = ['op_type' => $line['op_type'], 'date' => $line['date'],
                        'amount' => $line['amount'], 'currency' => $line['currency'], 'fees' => -1, ];
                } else {
                    $total = $this->rows[$line['user_id']][$year_month][$week]['total'];
                    $this->rows[$line['user_id']][$year_month][$week]['total'] = ++$total;
                    $this->rows[$line['user_id']][$year_month][$week]['transactions'][] = ['op_type' => $line['op_type'], 'date' => $line['date'],
                        'amount' => $line['amount'], 'currency' => $line['currency'], 'fees' => -1, ];
                }
            }
        } catch (Exception $ex) {
            $this->rows = [];
            parent::setErrorMessage(sprintf('An error occurred during parsing the csv file, more information about it here: %s', $ex->getMessage()));
        }

        return $this->rows;
    }

    /**
     * Get the JSON structure of the CSV transactions
     * 
     * @return string   JSON string on success, empty on error
     */
    public function generateJson(): string 
    {
        $str_json = '';
        if(empty($this->csv_rows))
            $str_json = '';

        $str_json = json_encode($this->csv_rows);
        return $str_json;
    }

    /**
     * Return the number of week which day belongs to.
     *
     * @param string $op_date Full date in Y-m-d format
     *
     * @return int The number of week, or 0 if invalid day
     */
    public static function weekOfDay(string $op_date): int
    {
        $num_week = 0;
        $day = explode('-', $op_date)[2];
        switch (true) {
            case $day > 0 && $day < 8:
                $num_week = 1;
            break;
            case $day > 7 && $day < 15:
                $num_week = 2;
            break;
            case $day > 14 && $day < 22:
                $num_week = 3;
            break;
            case $day > 21 && $day < 32:
                $num_week = 4;
            break;
            default:
                $num_week = 0;
            break;
        }

        return $num_week;
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
