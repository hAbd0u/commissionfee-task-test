<?php

declare(strict_types=1);

namespace CommissionFees\Tests\Service;

use PHPUnit\Framework\TestCase;
use CommissionFees\Service\Parser;

const CSV_TEST_FILE = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'test-input.csv';


class ParserTest extends TestCase
{

    /**
     * 
     * @dataProvider    daysDataProvider
     */
    public function testWeekOfDay(string $date, int $expected_result): void
    {
        $this->assertEquals(
            $expected_result,
            Parser::weekOfDay($date)
        );
    }

    public function testArrayFIllValuesKeys() {
        $arr = ['value1', 'value2', 'value3', 'value4'];
        $this->assertEquals(true, Parser::arrayFIllValuesKeys(['key1', 'key2', 'key3', 'key4'], $arr));

        $arr = ['value1', 'value2', 'value3'];
        $this->assertEquals(false, Parser::arrayFIllValuesKeys(['key1', 'key2', 'key3', 'key4'], $arr));

        $arr = ['value1', 'value2', 'value3', 'value4'];
        $this->assertEquals(false, Parser::arrayFIllValuesKeys(['key1', 'key2', 'key3'], $arr));
    }

    /**
     * 
     * @expectedException Exception
     */
    public function testParseFileNotFound() {
        $this->expectExceptionMessage('Csv file doesn\'t exist.', new Parser("CSV_TEST_FILE"));
    }

    public function testParseFile() {
        $parser = new Parser(CSV_TEST_FILE);
        $rows = $parser->parseFile();
        $this->assertEquals(true, !empty($rows));
    }

    /**
     * Dates for checking what week belongs to
     */
    public function daysDataProvider(): array 
    {
        return [
            ['2014-12-31', 4], 
            ['2015-01-01', 1], 
            ['2016-01-06', 1], 
            ['2016-02-06', 1], 
            ['2016-02-17', 3], 
            ['2016-03-17', 3], 
            ['2017-03-17', 3], 
            ['2016-01-10', 2],
            ['2016-02-15', 3],
            ['2016-02-19', 3],
            ['2016-02-22', 4],
            ['2016-02-28', 4],
            ['2016-02-29', 4],
            ['2016-02-30', 4],
            ['2016-02-31', 4],
            ['2016-02-32', 0],
            ['2016-02-00', 0],
        ];
    }
}
