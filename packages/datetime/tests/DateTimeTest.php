<?php

declare(strict_types=1);

namespace Tempest\DateTime\Tests;

use DateTimeImmutable;
use DateTimeZone;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use Tempest\DateTime\DateStyle;
use Tempest\DateTime\DateTime;
use Tempest\DateTime\Exception\UnexpectedValueException;
use Tempest\DateTime\FormatPattern;
use Tempest\DateTime\Meridiem;
use Tempest\DateTime\Month;
use Tempest\DateTime\TimeStyle;
use Tempest\DateTime\Timezone;
use Tempest\DateTime\Weekday;
use Tempest\Support\Language\Locale;

use function Tempest\DateTime\create_intl_date_formatter;
use function time;

/**
 * @mago-expect php-unit/strict-assertions
 */
final class DateTimeTest extends TestCase
{
    use DateTimeTestTrait;

    public function test_now(): void
    {
        $timestamp = DateTime::now()->getTimestamp();

        $this->assertEqualsWithDelta(time(), $timestamp->getSeconds(), 1);
    }

    public function test_today_at(): void
    {
        $now = DateTime::now();
        $today = DateTime::todayAt(14, 0o0, 0o0);

        $this->assertSame($now->getDate(), $today->getDate());
        $this->assertNotSame($now->getTime(), $today->getTime());
        $this->assertSame(14, $today->getHours());
        $this->assertSame(0, $today->getMinutes());
        $this->assertSame(0, $today->getSeconds());
        $this->assertSame(0, $today->getNanoseconds());
    }

    public function test_today_at_defaults(): void
    {
        $now = DateTime::now();
        $today = DateTime::todayAt(14, 0);

        $this->assertSame($now->getDate(), $today->getDate());
        $this->assertNotSame($now->getTime(), $today->getTime());
        $this->assertSame(14, $today->getHours());
        $this->assertSame(0, $today->getMinutes());
        $this->assertSame(0, $today->getSeconds());
        $this->assertSame(0, $today->getNanoseconds());
        $this->assertSame(Timezone::default(), $today->getTimezone());
    }

    public function test_from_parts(): void
    {
        $datetime = DateTime::fromParts(Timezone::UTC, 2024, Month::FEBRUARY, 4, 14, 0, 0, 1);

        $this->assertSame(Timezone::UTC, $datetime->getTimezone());
        $this->assertSame(2024, $datetime->getYear());
        $this->assertSame(24, $datetime->getYearShort());
        $this->assertSame(2, $datetime->getMonth());
        $this->assertSame(4, $datetime->getDay());
        $this->assertSame(Weekday::SUNDAY, $datetime->getWeekday());
        $this->assertSame(14, $datetime->getHours());
        $this->assertSame(0, $datetime->getMinutes());
        $this->assertSame(0, $datetime->getSeconds());
        $this->assertSame(1, $datetime->getNanoseconds());
        $this->assertSame([2024, 2, 4, 14, 0, 0, 1], $datetime->getParts());
    }

    public function test_from_parts_with_defaults(): void
    {
        $datetime = DateTime::fromParts(Timezone::UTC, 2024, Month::FEBRUARY, 4);

        $this->assertSame(Timezone::UTC, $datetime->getTimezone());
        $this->assertSame(2024, $datetime->getYear());
        $this->assertSame(2, $datetime->getMonth());
        $this->assertSame(4, $datetime->getDay());
        $this->assertSame(Weekday::SUNDAY, $datetime->getWeekday());
        $this->assertSame(0, $datetime->getHours());
        $this->assertSame(0, $datetime->getMinutes());
        $this->assertSame(0, $datetime->getSeconds());
        $this->assertSame(0, $datetime->getNanoseconds());
    }

    #[DataProvider('provide_invalid_component_parts')]
    public function test_from_parts_with_invalid_component(
        string $expectedMessage,
        int $year,
        int $month,
        int $day,
        int $hours,
        int $minutes,
        int $seconds,
        int $nanoseconds,
    ): void {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage($expectedMessage);

        DateTime::fromParts(Timezone::UTC, $year, $month, $day, $hours, $minutes, $seconds, $nanoseconds);
    }

    public static function provide_invalid_component_parts(): array
    {
        return [
            [
                'Unexpected year value encountered. Provided "0", but the calendar expects "1". Check the year for accuracy and ensure it\'s within the supported range.',
                0,
                1,
                1,
                0,
                0,
                0,
                0,
            ],
            [
                'Unexpected month value encountered. Provided "0", but the calendar expects "12". Ensure the month is within the 1-12 range and matches the specific year context.',
                2024,
                0,
                1,
                0,
                0,
                0,
                0,
            ],
            [
                'Unexpected day value encountered. Provided "0", but the calendar expects "31". Ensure the day is valid for the given month and year, considering variations like leap years.',
                2024,
                1,
                0,
                0,
                0,
                0,
                0,
            ],
            [
                'Unexpected hours value encountered. Provided "-1", but the calendar expects "23". Ensure the hour falls within a 24-hour day.',
                2024,
                1,
                1,
                -1,
                0,
                0,
                0,
            ],
            [
                'Unexpected minutes value encountered. Provided "-1", but the calendar expects "59". Check the minutes value for errors and ensure it\'s within the 0-59 range.',
                2024,
                1,
                1,
                0,
                -1,
                0,
                0,
            ],
            [
                'Unexpected seconds value encountered. Provided "59", but the calendar expects "-1". Ensure the seconds are correct and within the 0-59 range.',
                2024,
                1,
                1,
                0,
                0,
                -1,
                0,
            ],
        ];
    }

    public function test_from_string(): void
    {
        $timezone = Timezone::EUROPE_BRUSSELS;
        $datetime = DateTime::fromParts($timezone, 2024, Month::FEBRUARY, 4, 14, 0, 0, 0);

        $string = $datetime->toString();
        $parsed = DateTime::fromString($string, timezone: $timezone);

        $this->assertEquals($datetime->getTimestamp(), $parsed->getTimestamp());
        $this->assertSame($datetime->getTimezone(), $parsed->getTimezone());
        $this->assertSame($string, $parsed->toString());
    }

    public function test_to_string(): void
    {
        $datetime = DateTime::fromParts(Timezone::default(), 2024, Month::FEBRUARY, 4, 14, 0, 0, 0);

        $this->assertSame('4 Feb 2024, 14:00:00', $datetime->toString());
        $this->assertSame('04/02/2024, 14:00:00', $datetime->toString(dateStyle: DateStyle::SHORT));
        $this->assertSame(
            '4 Feb 2024, 14:00:00 Greenwich Mean Time',
            $datetime->toString(timeStyle: TimeStyle::FULL),
        );
        $this->assertSame('4 Feb 2024, 15:00:00', $datetime->toString(timezone: TimeZone::EUROPE_BRUSSELS));

        // Formatting depends on version of intl - so compare with intl version instead of hardcoding a label:
        $this->assertSame(
            create_intl_date_formatter(locale: Locale::DUTCH_BELGIUM)->format($datetime->getTimestamp()->getSeconds()),
            $datetime->toString(locale: Locale::DUTCH_BELGIUM),
        );
    }

    public function test_format(): void
    {
        $datetime = DateTime::fromParts(Timezone::default(), 2024, Month::FEBRUARY, 4, 14, 0, 0, 0);

        $this->assertSame('4 Feb 2024, 14:00:00', $datetime->format());
        $this->assertSame('02/04/2024', $datetime->format(pattern: FormatPattern::AMERICAN));
        $this->assertSame('02/04/2024', $datetime->format(pattern: FormatPattern::AMERICAN->value));
        $this->assertSame('4 Feb 2024, 15:00:00', $datetime->format(timezone: TimeZone::EUROPE_BRUSSELS));

        // Formatting depends on version of intl - so compare with intl version instead of hardcoding a label:
        $this->assertSame(
            create_intl_date_formatter(locale: Locale::DUTCH_BELGIUM)->format($datetime->getTimestamp()->getSeconds()),
            $datetime->toString(locale: Locale::DUTCH_BELGIUM),
        );
    }

    public function test_parse(): void
    {
        $datetime = DateTime::fromParts(Timezone::default(), 2024, Month::FEBRUARY, 4, 14, 0, 0, 0);

        $string = $datetime->format();
        $parsed = DateTime::fromPattern($string, pattern: FormatPattern::SHORT_DATE_WITH_TIME);

        $this->assertEquals($datetime->getTimestamp(), $parsed->getTimestamp());
        $this->assertSame($datetime->getTimezone(), $parsed->getTimezone());
    }

    public function test_parse_timestamp(): void
    {
        $expected = DateTime::fromTimestamp(1747670452940);
        $parsed = DateTime::parse(1747670452940);

        $this->assertEquals($expected->getTimestamp(), $parsed->getTimestamp());
        $this->assertSame($expected->getTimezone(), $parsed->getTimezone());
    }

    public function test_parse_from_native(): void
    {
        $expected = DateTime::fromParts(Timezone::default(), 2024, Month::JANUARY, 1, 10, 0, 0, 0);
        $parsed = DateTime::parse(new DateTimeImmutable('2024-01-01 10:00:00'));

        $this->assertEquals($expected->getTimestamp(), $parsed->getTimestamp());
        $this->assertSame($expected->getTimezone(), $parsed->getTimezone());
    }

    public function test_parse_from_native_with_timezone(): void
    {
        $expected = DateTime::fromParts(Timezone::default(), 2024, Month::JANUARY, 1, 10, 0, 0, 0)
            ->convertToTimezone(Timezone::AMERICA_NEW_YORK);

        $parsed = DateTime::parse(new DateTimeImmutable('2024-01-01 10:00:00')->setTimezone(new DateTimeZone('America/New_York')));

        $this->assertEquals($expected->getTimestamp(), $parsed->getTimestamp());
        $this->assertSame($expected->getTimezone(), $parsed->getTimezone());
    }

    public function test_parse_from_timestamp(): void
    {
        $expected = DateTime::fromParts(Timezone::default(), 2024, Month::JANUARY, 1, 10, 0, 0, 0);
        $parsed = DateTime::fromTimestamp(new DateTimeImmutable('2024-01-01 10:00:00')->getTimestamp());

        $this->assertEquals($expected->getTimestamp(), $parsed->getTimestamp());
        $this->assertSame($expected->getTimezone(), $parsed->getTimezone());
    }

    public function test_parse_from_timestamp_with_timezone(): void
    {
        $expected = DateTime::fromParts(Timezone::default(), 2024, Month::JANUARY, 1, 10, 0, 0, 0)
            ->convertToTimezone(Timezone::AMERICA_NEW_YORK);

        $parsed = DateTime::fromTimestamp(new DateTimeImmutable('2024-01-01 10:00:00')->getTimestamp(), timezone: Timezone::AMERICA_NEW_YORK);

        $this->assertEquals($expected->getTimestamp(), $parsed->getTimestamp());
        $this->assertSame($expected->getTimezone(), $parsed->getTimezone());
    }

    public function test_parse_with_timezone(): void
    {
        $datetime = DateTime::fromParts(Timezone::AMERICA_NEW_YORK, 2024, Month::FEBRUARY, 4, 14, 0, 0, 0);

        $string = $datetime->format();
        $parsed = DateTime::fromPattern($string, pattern: FormatPattern::SHORT_DATE_WITH_TIME, timezone: TimeZone::AMERICA_NEW_YORK);

        $this->assertEquals($datetime->getTimestamp(), $parsed->getTimestamp());
        $this->assertSame($datetime->getTimezone(), $parsed->getTimezone());
    }

    public function test_with_date(): void
    {
        $datetime = DateTime::fromParts(Timezone::default(), 2024, Month::FEBRUARY, 4, 14, 0, 0, 0);
        $new = $datetime->withDate(2025, Month::MARCH, 5);

        $this->assertSame(2025, $new->getYear());
        $this->assertSame(3, $new->getMonth());
        $this->assertSame(5, $new->getDay());
        $this->assertSame(14, $new->getHours());
        $this->assertSame(0, $new->getMinutes());
        $this->assertSame(0, $new->getSeconds());
        $this->assertSame(0, $new->getNanoseconds());
    }

    public function test_with_methods(): void
    {
        $datetime = DateTime::fromParts(Timezone::default(), 2024, Month::FEBRUARY, 4, 14, 0, 0, 0);

        $new = $datetime->withYear(2025);
        $this->assertSame(2025, $new->getYear());

        $new = $datetime->withMonth(Month::MARCH);
        $this->assertSame(3, $new->getMonth());

        $new = $datetime->withDay(5);
        $this->assertSame(5, $new->getDay());

        $new = $datetime->withHours(15);
        $this->assertSame(15, $new->getHours());

        $new = $datetime->withMinutes(30);
        $this->assertSame(30, $new->getMinutes());

        $new = $datetime->withSeconds(45);
        $this->assertSame(45, $new->getSeconds());

        $new = $datetime->withNanoseconds(100);
        $this->assertSame(100, $new->getNanoseconds());
    }

    public function test_get_era(): void
    {
        $datetime = DateTime::fromParts(Timezone::default(), 2024, Month::FEBRUARY, 4, 14, 0, 0, 0);

        $this->assertSame('AD', $datetime->getEra()->value);
    }

    public function test_get_century(): void
    {
        $this->assertSame(20, DateTime::fromParts(Timezone::default(), 1999, Month::FEBRUARY, 4, 14)->getCentury());
        $this->assertSame(21, DateTime::fromParts(Timezone::default(), 2000, Month::FEBRUARY, 4, 14)->getCentury());
    }

    #[TestWith([0, 12, Meridiem::ANTE_MERIDIEM])]
    #[TestWith([1, 1, Meridiem::ANTE_MERIDIEM])]
    #[TestWith([2, 2, Meridiem::ANTE_MERIDIEM])]
    #[TestWith([11, 11, Meridiem::ANTE_MERIDIEM])]
    #[TestWith([12, 12, Meridiem::POST_MERIDIEM])]
    #[TestWith([13, 1, Meridiem::POST_MERIDIEM])]
    #[TestWith([14, 2, Meridiem::POST_MERIDIEM])]
    #[TestWith([23, 11, Meridiem::POST_MERIDIEM])]
    public function test_get_twelve_hours(int $hour, int $expectedTwelveHour, Meridiem $expectedMeridiem): void
    {
        $datetime = DateTime::fromParts(Timezone::default(), 2024, Month::FEBRUARY, 4, $hour, 0, 0, 0);
        [$hours, $meridiem] = $datetime->getTwelveHours();

        $this->assertSame($expectedTwelveHour, $hours);
        $this->assertSame($expectedMeridiem, $meridiem);
    }

    public function test_get_iso_week(): void
    {
        $datetime = DateTime::fromParts(Timezone::default(), 2024, Month::FEBRUARY, 4, 14, 0, 0, 0);

        [$year, $week] = $datetime->getISOWeekNumber();

        $this->assertSame(2024, $year);
        $this->assertSame(5, $week);

        $datetime = DateTime::fromParts(Timezone::default(), 2023, Month::JANUARY, 1, 14, 0, 0, 0);

        [$year, $week] = $datetime->getISOWeekNumber();

        $this->assertSame(2022, $year);
        $this->assertSame(52, $week);

        $datetime = DateTime::fromParts(Timezone::default(), 2025, Month::DECEMBER, 31, 14, 0, 0, 0);

        [$year, $week] = $datetime->getISOWeekNumber();

        $this->assertSame(2026, $year);
        $this->assertSame(1, $week);
    }

    public function test_plus_methods(): void
    {
        $datetime = DateTime::fromParts(Timezone::default(), 2024, Month::FEBRUARY, 4, 14, 0, 0, 0);

        $this->assertSame(2025, $datetime->plusYears(1)->getYear());
        $this->assertSame(2025, $datetime->plusYear()->getYear());

        $this->assertSame(3, $datetime->plusMonths(1)->getMonth());
        $this->assertSame(3, $datetime->plusMonth()->getMonth());
        $this->assertSame($datetime, $datetime->plusMonths(0));
        $this->assertSame(1, $datetime->plusMonths(-1)->getMonth());

        $this->assertSame(5, $datetime->plusDays(1)->getDay());
        $this->assertSame(5, $datetime->plusDay()->getDay());

        $this->assertSame(15, $datetime->plusHours(1)->getHours());
        $this->assertSame(15, $datetime->plusHour()->getHours());

        $this->assertSame(1, $datetime->plusMinutes(1)->getMinutes());
        $this->assertSame(1, $datetime->plusMinute()->getMinutes());

        $this->assertSame(1, $datetime->plusSeconds(1)->getSeconds());
        $this->assertSame(1, $datetime->plusSecond()->getSeconds());

        $this->assertSame(1, $datetime->plusNanoseconds(1)->getNanoseconds());
        $this->assertSame(1, $datetime->plusNanosecond()->getNanoseconds());
    }

    public function test_plus_months_edge_cases(): void
    {
        $jan_31th = DateTime::fromParts(Timezone::default(), 2024, Month::JANUARY, 31, 14, 0, 0, 0);
        $febr_29th = $jan_31th->plusMonths(1);
        $this->assertSame([2024, 2, 29], $febr_29th->getDate());
        $this->assertSame([14, 0, 0, 0], $febr_29th->getTime());

        $dec_31th = DateTime::fromParts(Timezone::default(), 2023, Month::DECEMBER, 31, 14, 0, 0, 0);
        $march_31th = $dec_31th->plusMonths(3);
        $this->assertSame([2024, 3, 31], $march_31th->getDate());
        $this->assertSame([14, 0, 0, 0], $march_31th->getTime());

        $april_30th = $march_31th->plusMonths(1);
        $this->assertSame([2024, 4, 30], $april_30th->getDate());
        $this->assertSame([14, 0, 0, 0], $april_30th->getTime());

        $april_30th_next_year = $april_30th->plusYears(1);
        $this->assertSame([2025, 4, 30], $april_30th_next_year->getDate());
        $this->assertSame([14, 0, 0, 0], $april_30th_next_year->getTime());
    }

    public function test_plus_month_overflows(): void
    {
        $jan_31th_2024 = DateTime::fromParts(Timezone::default(), 2024, Month::JANUARY, 31, 14, 0, 0, 0);
        $previous_month = 1;
        for ($i = 1; $i < 24; $i++) {
            $res = $jan_31th_2024->plusMonths($i);

            $expected_month = ($previous_month + 1) % 12;
            $expected_month = $expected_month === 0 ? 12 : $expected_month;

            $this->assertSame($res->getDay(), $res->getMonthEnum()->getDaysForYear($res->getYear()));
            $this->assertSame($res->getMonth(), $expected_month);

            $previous_month = $expected_month;
        }
    }

    public function test_minus_methods(): void
    {
        $datetime = DateTime::fromParts(Timezone::default(), 2024, Month::FEBRUARY, 4, 14, 0, 0, 0);

        $this->assertSame(2023, $datetime->minusYears(1)->getYear());
        $this->assertSame(2023, $datetime->minusYear()->getYear());

        $this->assertSame(1, $datetime->minusMonths(1)->getMonth());
        $this->assertSame(1, $datetime->minusMonth()->getMonth());
        $this->assertSame(3, $datetime->minusMonths(-1)->getMonth());
        $this->assertSame($datetime, $datetime->minusMonths(0));

        $this->assertSame(3, $datetime->minusDays(1)->getDay());
        $this->assertSame(3, $datetime->minusDay()->getDay());

        $this->assertSame(13, $datetime->minusHours(1)->getHours());
        $this->assertSame(13, $datetime->minusHour()->getHours());

        $this->assertSame(59, $datetime->minusMinutes(1)->getMinutes());
        $this->assertSame(59, $datetime->minusMinute()->getMinutes());

        $this->assertSame(59, $datetime->minusSeconds(1)->getSeconds());
        $this->assertSame(59, $datetime->minusSecond()->getSeconds());

        $this->assertSame(999999999, $datetime->minusNanoseconds(1)->getNanoseconds());
        $this->assertSame(999999999, $datetime->minusNanosecond()->getNanoseconds());
    }

    public function test_minus_months_edge_cases(): void
    {
        $febr_29th = DateTime::fromParts(Timezone::default(), 2024, Month::FEBRUARY, 29, 14, 0, 0, 0);
        $jan_29th = $febr_29th->minusMonths(1);
        $this->assertSame([2024, 1, 29], $jan_29th->getDate());
        $this->assertSame([14, 0, 0, 0], $jan_29th->getTime());

        $febr_28th_previous_year = $febr_29th->minusYears(1);
        $this->assertSame([2023, 2, 28], $febr_28th_previous_year->getDate());
        $this->assertSame([14, 0, 0, 0], $febr_28th_previous_year->getTime());

        $febr_29th_previous_leap_year = $febr_29th->minusYears(4);
        $this->assertSame([2020, 2, 29], $febr_29th_previous_leap_year->getDate());
        $this->assertSame([14, 0, 0, 0], $febr_29th_previous_leap_year->getTime());

        $march_31th = DateTime::fromParts(Timezone::default(), 2024, Month::MARCH, 31, 14, 0, 0, 0);
        $dec_31th = $march_31th->minusMonths(3);
        $this->assertSame([2023, 12, 31], $dec_31th->getDate());
        $this->assertSame([14, 0, 0, 0], $dec_31th->getTime());

        $jan_31th = $march_31th->minusMonths(2);
        $this->assertSame([2024, 1, 31], $jan_31th->getDate());
        $this->assertSame([14, 0, 0, 0], $jan_31th->getTime());

        $may_31th = DateTime::fromParts(Timezone::default(), 2024, Month::MAY, 31, 14, 0, 0, 0);
        $april_30th = $may_31th->minusMonths(1);
        $this->assertSame([2024, 4, 30], $april_30th->getDate());
        $this->assertSame([14, 0, 0, 0], $april_30th->getTime());

        $april_30th_previous_year = $april_30th->minusYears(1);
        $this->assertSame([2023, 4, 30], $april_30th_previous_year->getDate());
        $this->assertSame([14, 0, 0, 0], $april_30th_previous_year->getTime());
    }

    public function test_minus_month_overflows(): void
    {
        $jan_31th_2024 = DateTime::fromParts(Timezone::default(), 2024, Month::JANUARY, 31, 14, 0, 0, 0);
        $previous_month = 1;
        for ($i = 1; $i < 24; $i++) {
            $res = $jan_31th_2024->minusMonths($i);

            $expected_month = $previous_month - 1;
            $expected_month = $expected_month === 0 ? 12 : $expected_month;

            $this->assertSame($res->getDay(), $res->getMonthEnum()->getDaysForYear($res->getYear()));
            $this->assertSame($res->getMonth(), $expected_month);

            $previous_month = $expected_month;
        }
    }

    public function test_is_leap_year(): void
    {
        $datetime = DateTime::fromParts(Timezone::default(), 2024, Month::FEBRUARY, 4, 14, 0, 0, 0);

        $this->assertTrue($datetime->isLeapYear());

        $datetime = DateTime::fromParts(Timezone::default(), 2023, Month::FEBRUARY, 4, 14, 0, 0, 0);

        $this->assertFalse($datetime->isLeapYear());
    }

    public function test_to_rfc3999(): void
    {
        $datetime = DateTime::fromParts(Timezone::UTC, 2024, Month::FEBRUARY, 4, 14, 0, 0, 0);

        $this->assertSame('2024-02-04T14:00:00+00:00', $datetime->toRfc3339());
    }

    public function test_equal_including_timezone(): void
    {
        $datetime1 = DateTime::fromParts(Timezone::UTC, 2024, Month::FEBRUARY, 4, 14, 0, 0, 0);
        $datetime2 = DateTime::fromParts(Timezone::default(), 2024, Month::FEBRUARY, 4, 14, 0, 0, 0);

        $this->assertTrue($datetime1->equals($datetime2));
        $this->assertFalse($datetime1->equalsIncludingTimezone($datetime2));

        $datetime1 = DateTime::fromParts(Timezone::UTC, 2024, Month::FEBRUARY, 4, 14, 0, 0, 0);
        $datetime2 = DateTime::fromParts(Timezone::UTC, 2024, Month::FEBRUARY, 4, 14, 0, 0, 0);

        $this->assertTrue($datetime1->equals($datetime2));
        $this->assertTrue($datetime1->equalsIncludingTimezone($datetime2));

        $datetime1 = DateTime::fromParts(Timezone::default(), 2024, Month::FEBRUARY, 4, 14, 0, 0, 0);
        $datetime2 = DateTime::fromParts(Timezone::default(), 2024, Month::FEBRUARY, 4, 14, 0, 0, 0);

        $this->assertTrue($datetime1->equals($datetime2));
        $this->assertTrue($datetime1->equalsIncludingTimezone($datetime2));

        $datetime1 = DateTime::fromParts(Timezone::default(), 2024, Month::FEBRUARY, 4, 14, 0, 0, 0);
        $datetime2 = DateTime::fromParts(Timezone::default(), 2024, Month::FEBRUARY, 4, 15, 0, 0, 0);

        $this->assertFalse($datetime1->equals($datetime2));
        $this->assertFalse($datetime1->equalsIncludingTimezone($datetime2));
    }

    public function test_json_serialize(): void
    {
        $datetime = DateTime::fromParts(Timezone::default(), 2024, Month::FEBRUARY, 4, 14, 0, 0, 0);

        $this->assertSame(
            '{"timezone":"Europe\/London","timestamp":{"seconds":1707055200,"nanoseconds":0},"year":2024,"month":2,"day":4,"hours":14,"minutes":0,"seconds":0,"nanoseconds":0}',
            json_encode($datetime),
        );
    }

    public function test_with_time(): void
    {
        $date = DateTime::todayAt(14, 0);
        $new = $date->withTime(15, 0);

        $this->assertSame(15, $new->getHours());
        $this->assertSame(0, $new->getMinutes());
        $this->assertSame(0, $new->getSeconds());
        $this->assertSame(0, $new->getNanoseconds());
    }

    public function test_timezone_info(): void
    {
        $timeZone = Timezone::EUROPE_BRUSSELS;
        $date = DateTime::fromParts($timeZone, 2024, 0o1, 0o1);

        $this->assertSame(! $timeZone->getDaylightSavingTimeOffset($date)->isZero(), $date->isDaylightSavingTime());
        $this->assertEquals($timeZone->getOffset($date), $date->getTimezoneOffset());
    }

    public function test_convert_time_zone(): void
    {
        $date = DateTime::fromParts(Timezone::EUROPE_BRUSSELS, 2024, 0o1, 0o1, 1);
        $converted = $date->convertToTimezone($london = Timezone::EUROPE_LONDON);

        $this->assertSame($london, $converted->getTimezone());
        $this->assertSame($date->getTimestamp(), $converted->getTimestamp());
        $this->assertSame($date->getYear(), $converted->getYear());
        $this->assertSame($date->getMonth(), $converted->getMonth());
        $this->assertSame($date->getDay(), $converted->getDay());
        $this->assertSame(0, $converted->getHours());
    }
}
