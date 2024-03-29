<?php

namespace Pantono\Utilities\Tests;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use Pantono\Utilities\DateTimeParser;

class DateTimeParserTest extends TestCase
{
    #[DataProvider('dateDataProvider')]
    public function testDates(string $input, mixed $expected)
    {
        $this->assertEquals($expected, DateTimeParser::parseDate($input));
    }

    public static function dateDataProvider(): array
    {
        return [
            ['2012-01-01', new \DateTime('2012-01-01 00:00:00')],
            ['2012-01-01 23:59:59', new \DateTime('2012-01-01 23:59:59')],
            ['01/01/2023', new \DateTime('2023-01-01 00:00:00')],
            ['01/01/2023 23:00:00', new \DateTime('2023-01-01 23:00:00')],
        ];
    }
}
