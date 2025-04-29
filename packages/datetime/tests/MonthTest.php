<?php

declare(strict_types=1);

namespace Tempest\DateTime\Tests;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Tempest\DateTime\Month;

final class MonthTest extends TestCase
{
    use DateTimeTestTrait;

    #[DataProvider('provideGetPreviousData')]
    public function test_get_previous(Month $month, Month $expected): void
    {
        $this->assertSame($expected, $month->getPrevious());
    }

    #[DataProvider('provideGetNextData')]
    public function test_get_next(Month $month, Month $expected): void
    {
        $this->assertSame($expected, $month->getNext());
    }

    #[DataProvider('provideGetDaysData')]
    public function test_get_days(Month $month, int $expectedForLeapYear, int $expectedForNonLeapYear): void
    {
        $this->assertSame($expectedForLeapYear, $month->getLeapYearDays());
        $this->assertSame($expectedForNonLeapYear, $month->getNonLeapYearDays());
    }

    #[DataProvider('provideGetDaysForYearData')]
    public function test_get_days_for_year(Month $month, int $year, int $expected): void
    {
        $this->assertSame($expected, $month->getDaysForYear($year));
    }

    /**
     * @return iterable<array{Month, Month}>
     */
    public static function provideGetPreviousData(): iterable
    {
        yield [Month::JANUARY, Month::DECEMBER];
        yield [Month::FEBRUARY, Month::JANUARY];
        yield [Month::MARCH, Month::FEBRUARY];
        yield [Month::APRIL, Month::MARCH];
        yield [Month::MAY, Month::APRIL];
        yield [Month::JUNE, Month::MAY];
        yield [Month::JULY, Month::JUNE];
        yield [Month::AUGUST, Month::JULY];
        yield [Month::SEPTEMBER, Month::AUGUST];
        yield [Month::OCTOBER, Month::SEPTEMBER];
        yield [Month::NOVEMBER, Month::OCTOBER];
        yield [Month::DECEMBER, Month::NOVEMBER];
    }

    /**
     * @return iterable<array{Month, Month}>
     */
    public static function provideGetNextData(): iterable
    {
        yield [Month::JANUARY, Month::FEBRUARY];
        yield [Month::FEBRUARY, Month::MARCH];
        yield [Month::MARCH, Month::APRIL];
        yield [Month::APRIL, Month::MAY];
        yield [Month::MAY, Month::JUNE];
        yield [Month::JUNE, Month::JULY];
        yield [Month::JULY, Month::AUGUST];
        yield [Month::AUGUST, Month::SEPTEMBER];
        yield [Month::SEPTEMBER, Month::OCTOBER];
        yield [Month::OCTOBER, Month::NOVEMBER];
        yield [Month::NOVEMBER, Month::DECEMBER];
        yield [Month::DECEMBER, Month::JANUARY];
    }

    /**
     * @return iterable<array{Month, int, int}>
     */
    public static function provideGetDaysData(): iterable
    {
        yield [Month::JANUARY, 31, 31];
        yield [Month::FEBRUARY, 29, 28];
        yield [Month::MARCH, 31, 31];
        yield [Month::APRIL, 30, 30];
        yield [Month::MAY, 31, 31];
        yield [Month::JUNE, 30, 30];
        yield [Month::JULY, 31, 31];
        yield [Month::AUGUST, 31, 31];
        yield [Month::SEPTEMBER, 30, 30];
        yield [Month::OCTOBER, 31, 31];
        yield [Month::NOVEMBER, 30, 30];
        yield [Month::DECEMBER, 31, 31];
    }

    /**
     * @return iterable<array{Month, int, int}>
     */
    public static function provideGetDaysForYearData(): iterable
    {
        yield [Month::JANUARY, 2024, 31];
        yield [Month::FEBRUARY, 2024, 29];
        yield [Month::MARCH, 2024, 31];
        yield [Month::APRIL, 2024, 30];
        yield [Month::MAY, 2024, 31];
        yield [Month::JUNE, 2024, 30];
        yield [Month::JULY, 2024, 31];
        yield [Month::AUGUST, 2024, 31];
        yield [Month::SEPTEMBER, 2024, 30];
        yield [Month::OCTOBER, 2024, 31];
        yield [Month::NOVEMBER, 2024, 30];
        yield [Month::DECEMBER, 2024, 31];
    }
}
