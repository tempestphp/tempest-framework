<?php

declare(strict_types=1);

namespace Tempest\DateTime\Tests;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psl\Async;
use Tempest\DateTime\DateTime;
use Tempest\DateTime\Duration;
use Tempest\DateTime\Exception\OverflowException;
use Tempest\DateTime\Exception\ParserException;
use Tempest\DateTime\Exception\UnderflowException;
use Tempest\DateTime\FormatPattern;
use Tempest\DateTime\SecondsStyle;
use Tempest\DateTime\Timestamp;
use Tempest\DateTime\Timezone;
use Tempest\Intl\Locale;
use Tempest\Support\Comparison\Order;
use Tempest\Support\Math;

use function Tempest\DateTime\create_intl_date_formatter;
use function time;

use const Tempest\DateTime\NANOSECONDS_PER_SECOND;

final class TimestampTest extends TestCase
{
    use DateTimeTestTrait;

    public function test_now(): void
    {
        $timestamp = Timestamp::now();

        $this->assertEQUALsWithDelta(time(), $timestamp->getSeconds(), 1);
    }

    public function test_monotonic(): void
    {
        $timestamp = Timestamp::monotonic();

        $this->assertEQUALsWithDelta(time(), $timestamp->getSeconds(), 1);
    }

    public function test_since(): void
    {
        $a = Timestamp::fromParts(20, 1);
        $b = Timestamp::fromParts(30, 2);

        $duration = $b->since($a);

        $this->assertSame(10, $duration->getSeconds());
        $this->assertSame(1, $duration->getNanoseconds());
    }

    public function test_from_row_overflow(): void
    {
        $this->expectException(OverflowException::class);
        $this->expectExceptionMessage('Adding nanoseconds would cause an overflow.');

        Timestamp::fromParts(Math\INT64_MAX, NANOSECONDS_PER_SECOND);
    }

    public function test_from_row_underflow(): void
    {
        $this->expectException(UnderflowException::class);
        $this->expectExceptionMessage('Subtracting nanoseconds would cause an underflow.');

        Timestamp::fromParts(Math\INT64_MIN, -NANOSECONDS_PER_SECOND);
    }

    public function test_from_row_simplifies_nanoseconds(): void
    {
        $timestamp = Timestamp::fromParts(0, NANOSECONDS_PER_SECOND * 20);

        $this->assertSame(20, $timestamp->getSeconds());
        $this->assertSame(0, $timestamp->getNanoseconds());

        $timestamp = Timestamp::fromParts(0, 100 + (NANOSECONDS_PER_SECOND * 20));

        $this->assertSame(20, $timestamp->getSeconds());
        $this->assertSame(100, $timestamp->getNanoseconds());

        $timestamp = Timestamp::fromParts(30, -NANOSECONDS_PER_SECOND * 20);

        $this->assertSame(10, $timestamp->getSeconds());
        $this->assertSame(0, $timestamp->getNanoseconds());

        $timestamp = Timestamp::fromParts(10, 100 + (-NANOSECONDS_PER_SECOND * 20));

        $this->assertSame(-10, $timestamp->getSeconds());
        $this->assertSame(100, $timestamp->getNanoseconds());
    }

    public function test_parsing_from_pattern(): void
    {
        $timestamp = Timestamp::fromPattern(
            rawString: '2024 091',
            pattern: FormatPattern::JULIAN_DAY,
        );

        $datetime = DateTime::fromTimestamp($timestamp, Timezone::UTC);

        $this->assertSame(2024, $datetime->getYear());
        $this->assertSame(3, $datetime->getMonth());
        $this->assertSame(31, $datetime->getDay());
    }

    public function test_from_pattern_fails(): void
    {
        $this->expectException(ParserException::class);
        $this->expectExceptionMessage("Unable to interpret '2' as a valid date/time using pattern 'yyyy DDD'.");

        Timestamp::fromPattern('2', pattern: FormatPattern::JULIAN_DAY);
    }

    public function test_parse_format(): void
    {
        $a = Timestamp::now();
        $string = $a->format();

        $b = Timestamp::fromPattern($string, FormatPattern::SHORT_DATE_WITH_TIME);

        $this->assertSame($a->getSeconds(), $b->getSeconds());
    }

    public function test_from_string_to_string(): void
    {
        $a = Timestamp::now();
        $string = $a->toString();

        $b = Timestamp::fromString($string);

        $this->assertSame($a->getSeconds(), $b->getSeconds());
    }

    public function test_parse_fails(): void
    {
        $this->expectException(ParserException::class);
        $this->expectExceptionMessage("Unable to interpret 'x' as a valid date/time using pattern 'yyyy-MM-dd'T'HH:mm:ss.SSSXXX'.");

        Timestamp::fromPattern('x', FormatPattern::default());
    }

    public static function provide_format_parsing_data(): iterable
    {
        yield [
            1711917897,
            FormatPattern::FULL_DATE_TIME,
            Timezone::UTC,
            Locale::ENGLISH,
            'Sunday, March 31, 2024 20:44:57',
        ];
        yield [
            1711917897,
            FormatPattern::FULL_DATE_TIME,
            Timezone::ASIA_SHANGHAI,
            Locale::CHINESE_TRADITIONAL,
            '星期一, 4月 01, 2024 04:44:57',
        ];
        yield [
            1711917897,
            FormatPattern::COOKIE,
            Timezone::AMERICA_NEW_YORK,
            Locale::ENGLISH_UNITED_STATES,
            'Sunday, 31-Mar-2024 16:44:57 EDT',
        ];
        yield [
            1711917897,
            FormatPattern::HTTP,
            Timezone::EUROPE_VIENNA,
            Locale::GERMAN_AUSTRIA,
            'So., 31 März 2024 22:44:57 MESZ',
        ];
        yield [
            1711917897,
            FormatPattern::EMAIL,
            Timezone::EUROPE_MADRID,
            Locale::SPANISH_SPAIN,
            'dom, 31 mar 2024 22:44:57 GMT+02:00',
        ];
        yield [
            1711917897,
            FormatPattern::SQL_DATE_TIME,
            Timezone::AFRICA_TUNIS,
            Locale::ARABIC_TUNISIA,
            '2024-03-31 21:44:57',
        ];
        yield [1711832400, FormatPattern::ISO_ORDINAL_DATE, Timezone::EUROPE_MOSCOW, Locale::RUSSIAN_RUSSIA, '2024-091'];
        yield [
            1711917897,
            FormatPattern::ISO8601,
            Timezone::EUROPE_LONDON,
            Locale::ENGLISH_UNITED_KINGDOM,
            '2024-03-31T21:44:57.000+01:00',
        ];
    }

    #[DataProvider('provide_format_parsing_data')]
    public function test_formatting_and_pattern_parsing(int $timestamp, string|FormatPattern $pattern, Timezone $timezone, Locale $locale, string $expected): void
    {
        $timestamp = Timestamp::fromParts($timestamp);

        $result = $timestamp->format(
            pattern: $pattern,
            timezone: $timezone,
            locale: $locale,
        );

        $this->assertSame($expected, $result);

        $other = Timestamp::fromPattern($result, pattern: $pattern, timezone: $timezone, locale: $locale);

        $this->assertSame($timestamp->getSeconds(), $other->getSeconds());
        $this->assertSame($timestamp->getNanoseconds(), $other->getNanoseconds());
    }

    public function test_to_raw(): void
    {
        $timestamp = Timestamp::fromParts(12, 10);
        $parts = $timestamp->toParts();

        $this->assertSame(12, $parts[0]);
        $this->assertSame(10, $parts[1]);
    }

    /**
     * @return list<array{Timestamp, Timestamp, Order}>
     */
    public static function provide_compare_data(): array
    {
        return [
            [Timestamp::fromParts(100),  Timestamp::fromParts(42),   Order::GREATER],
            [Timestamp::fromParts(42),   Timestamp::fromParts(42),   Order::EQUAL],
            [Timestamp::fromParts(42),   Timestamp::fromParts(100),  Order::LESS],
            // Nanoseconds
            [Timestamp::fromParts(42, 100), Timestamp::fromParts(42, 42), Order::GREATER],
            [Timestamp::fromParts(42, 42), Timestamp::fromParts(42, 42), Order::EQUAL],
            [Timestamp::fromParts(42, 42), Timestamp::fromParts(42, 100), Order::LESS],
        ];
    }

    #[DataProvider('provide_compare_data')]
    public function test_compare(Timestamp $a, Timestamp $b, Order $expected): void
    {
        $opposite = Order::from(-$expected->value);

        $this->assertSame($expected, $a->compare($b));
        $this->assertSame($opposite, $b->compare($a));
        $this->assertSame($expected === Order::EQUAL, $a->equals($b));
        $this->assertSame($expected === Order::LESS, $a->before($b));
        $this->assertSame($expected !== Order::GREATER, $a->beforeOrAtTheSameTime($b));
        $this->assertSame($expected === Order::GREATER, $a->after($b));
        $this->assertSame($expected !== Order::LESS, $a->afterOrAtTheSameTime($b));
        $this->assertFalse($a->betweenTimeExclusive($a, $a));
        $this->assertFalse($a->betweenTimeExclusive($a, $b));
        $this->assertFalse($a->betweenTimeExclusive($b, $a));
        $this->assertFalse($a->betweenTimeExclusive($b, $b));
        $this->assertTrue($a->betweenTimeInclusive($a, $a));
        $this->assertTrue($a->betweenTimeInclusive($a, $b));
        $this->assertTrue($a->betweenTimeInclusive($b, $a));
        $this->assertSame($expected === Order::EQUAL, $a->betweenTimeInclusive($b, $b));
    }

    public function test_nanoseconds_modifications(): void
    {
        $timestamp = Timestamp::fromParts(0, 100);

        $this->assertSame(100, $timestamp->getNanoseconds());

        $timestamp = $timestamp->plus(Duration::nanoseconds(10));

        $this->assertSame(110, $timestamp->getNanoseconds());

        $timestamp = $timestamp->plus(Duration::nanoseconds(-10));

        $this->assertSame(100, $timestamp->getNanoseconds());

        $timestamp = $timestamp->minus(Duration::nanoseconds(-10));

        $this->assertSame(110, $timestamp->getNanoseconds());

        $timestamp = $timestamp->minus(Duration::nanoseconds(10));

        $this->assertSame(100, $timestamp->getNanoseconds());

        $timestamp = $timestamp->plusNanoseconds(10);

        $this->assertSame(110, $timestamp->getNanoseconds());

        $timestamp = $timestamp->plusNanoseconds(-10);

        $this->assertSame(100, $timestamp->getNanoseconds());

        $timestamp = $timestamp->minusNanoseconds(-10);

        $this->assertSame(110, $timestamp->getNanoseconds());

        $timestamp = $timestamp->minusNanoseconds(10);

        $this->assertSame(100, $timestamp->getNanoseconds());
    }

    public function test_seconds_modifications(): void
    {
        $timestamp = Timestamp::fromParts(5);

        $this->assertSame(5, $timestamp->getSeconds());

        $timestamp = $timestamp->plus(Duration::seconds(1));

        $this->assertSame(6, $timestamp->getSeconds());

        $timestamp = $timestamp->plus(Duration::seconds(-1));

        $this->assertSame(5, $timestamp->getSeconds());

        $timestamp = $timestamp->minus(Duration::seconds(-1));

        $this->assertSame(6, $timestamp->getSeconds());

        $timestamp = $timestamp->minus(Duration::seconds(1));

        $this->assertSame(5, $timestamp->getSeconds());

        $timestamp = $timestamp->plusSeconds(1);

        $this->assertSame(6, $timestamp->getSeconds());

        $timestamp = $timestamp->plusSeconds(-1);

        $this->assertSame(5, $timestamp->getSeconds());

        $timestamp = $timestamp->minusSeconds(-1);

        $this->assertSame(6, $timestamp->getSeconds());

        $timestamp = $timestamp->minusSeconds(1);

        $this->assertSame(5, $timestamp->getSeconds());
    }

    public function test_minute_modifications(): void
    {
        $timestamp = Timestamp::fromParts(5);

        $this->assertSame(5, $timestamp->getSeconds());

        $timestamp = $timestamp->plus(Duration::minutes(1));

        $this->assertSame(65, $timestamp->getSeconds());

        $timestamp = $timestamp->plus(Duration::minutes(-1));

        $this->assertSame(5, $timestamp->getSeconds());

        $timestamp = $timestamp->minus(Duration::minutes(-1));

        $this->assertSame(65, $timestamp->getSeconds());

        $timestamp = $timestamp->minus(Duration::minutes(1));

        $this->assertSame(5, $timestamp->getSeconds());

        $timestamp = $timestamp->plusMinutes(1);

        $this->assertSame(65, $timestamp->getSeconds());

        $timestamp = $timestamp->plusMinutes(-1);

        $this->assertSame(5, $timestamp->getSeconds());

        $timestamp = $timestamp->minusMinutes(-1);

        $this->assertSame(65, $timestamp->getSeconds());

        $timestamp = $timestamp->minusMinutes(1);

        $this->assertSame(5, $timestamp->getSeconds());
    }

    public function test_hour_modifications(): void
    {
        $timestamp = Timestamp::fromParts(5);

        $this->assertSame(5, $timestamp->getSeconds());

        $timestamp = $timestamp->plus(Duration::hours(1));

        $this->assertSame(3605, $timestamp->getSeconds());

        $timestamp = $timestamp->plus(Duration::hours(-1));

        $this->assertSame(5, $timestamp->getSeconds());

        $timestamp = $timestamp->minus(Duration::hours(-1));

        $this->assertSame(3605, $timestamp->getSeconds());

        $timestamp = $timestamp->minus(Duration::hours(1));

        $this->assertSame(5, $timestamp->getSeconds());

        $timestamp = $timestamp->plusHours(1);

        $this->assertSame(3605, $timestamp->getSeconds());

        $timestamp = $timestamp->plusHours(-1);

        $this->assertSame(5, $timestamp->getSeconds());

        $timestamp = $timestamp->minusHours(-1);

        $this->assertSame(3605, $timestamp->getSeconds());

        $timestamp = $timestamp->minusHours(1);

        $this->assertSame(5, $timestamp->getSeconds());
    }

    public function test_convert_to_timezone(): void
    {
        $timestamp = Timestamp::fromParts(1_711_917_232, 501_000_000);

        $this->assertSame(
            '2024-03-31T20:33:52.501Z',
            $timestamp->convertToTimezone(Timezone::UTC)->format(pattern: FormatPattern::ISO8601),
        );

        $this->assertSame(
            '2024-03-31T21:33:52.501+01:00',
            $timestamp->convertToTimezone(Timezone::AFRICA_TUNIS)->format(pattern: FormatPattern::ISO8601),
        );

        $this->assertSame(
            '2024-03-31T16:33:52.501-04:00',
            $timestamp->convertToTimezone(Timezone::AMERICA_NEW_YORK)->format(pattern: FormatPattern::ISO8601),
        );

        $this->assertSame(
            '2024-04-01T04:33:52.501+08:00',
            $timestamp->convertToTimezone(Timezone::ASIA_SHANGHAI)->format(pattern: FormatPattern::ISO8601),
        );
    }

    public function test_json_serialization(): void
    {
        $serialized = Timestamp::fromParts(1711917232, 12)->jsonSerialize();

        $this->assertSame(1711917232, $serialized['seconds']);
        $this->assertSame(12, $serialized['nanoseconds']);
    }

    public function test_to_rfc3999(): void
    {
        $timestamp = Timestamp::fromParts(1711917232, 12);

        $this->assertSame('2024-03-31T20:33:52.12+00:00', $timestamp->toRfc3339());
        $this->assertSame('2024-03-31T20:33:52+00:00', $timestamp->toRfc3339(secondsStyle: SecondsStyle::Seconds));
        $this->assertSame('2024-03-31T20:33:52.12Z', $timestamp->toRfc3339(useZ: true));
    }

    public function test_temporal_convenience_methods(): void
    {
        $timestamp1 = Timestamp::now();
        $timestamp2 = $timestamp1->plusMilliseconds(300);
        $timestamp3 = $timestamp2->plusMilliseconds(300);

        $this->assertTrue($timestamp1->isBefore($timestamp2));
        $this->assertFalse($timestamp2->isBefore($timestamp1));

        $this->assertTrue($timestamp2->isAfter($timestamp1));
        $this->assertFalse($timestamp1->isAfter($timestamp2));

        $this->assertTrue($timestamp1->isBeforeOrAt($timestamp2));
        $this->assertTrue($timestamp1->isBeforeOrAt($timestamp1));
        $this->assertFalse($timestamp2->isBeforeOrAt($timestamp1));

        $this->assertTrue($timestamp2->isAfterOrAt($timestamp1));
        $this->assertTrue($timestamp2->isAfterOrAt($timestamp2));
        $this->assertFalse($timestamp1->isAfterOrAt($timestamp2));

        $this->assertTrue($timestamp2->isBetween($timestamp1, $timestamp3));
        $this->assertTrue($timestamp1->isBetween($timestamp1, $timestamp3));
        $this->assertTrue($timestamp3->isBetween($timestamp1, $timestamp3));
        $this->assertFalse($timestamp1->isBetween($timestamp2, $timestamp3));

        $this->assertTrue($timestamp2->isBetweenExclusive($timestamp1, $timestamp3));
        $this->assertFalse($timestamp1->isBetweenExclusive($timestamp1, $timestamp3));
        $this->assertFalse($timestamp3->isBetweenExclusive($timestamp1, $timestamp3));

        $this->assertTrue($timestamp1->isSameTime($timestamp1));
        $this->assertFalse($timestamp1->isSameTime($timestamp2));
    }

    public function test_at_the_same_time_edge_cases(): void
    {
        $timestamp1 = Timestamp::fromParts(1234567890, 123456789);
        $timestamp2 = Timestamp::fromParts(1234567890, 123456789);
        $timestamp3 = Timestamp::fromParts(1234567890, 123456790);

        $this->assertTrue($timestamp1->atTheSameTime($timestamp2));
        $this->assertFalse($timestamp1->atTheSameTime($timestamp3));
        $this->assertTrue($timestamp1->isSameTime($timestamp2));
        $this->assertFalse($timestamp1->isSameTime($timestamp3));
    }

    public function test_between_time_boundary_conditions(): void
    {
        $start = Timestamp::fromParts(1000, 0);
        $middle = Timestamp::fromParts(1500, 0);
        $end = Timestamp::fromParts(2000, 0);
        $before = Timestamp::fromParts(500, 0);
        $after = Timestamp::fromParts(2500, 0);

        $this->assertTrue($middle->betweenTimeInclusive($start, $end));
        $this->assertTrue($start->betweenTimeInclusive($start, $end));
        $this->assertTrue($end->betweenTimeInclusive($start, $end));
        $this->assertFalse($before->betweenTimeInclusive($start, $end));
        $this->assertFalse($after->betweenTimeInclusive($start, $end));

        $this->assertTrue($middle->betweenTimeExclusive($start, $end));
        $this->assertFalse($start->betweenTimeExclusive($start, $end));
        $this->assertFalse($end->betweenTimeExclusive($start, $end));
        $this->assertFalse($before->betweenTimeExclusive($start, $end));
        $this->assertFalse($after->betweenTimeExclusive($start, $end));

        $this->assertTrue($middle->isBetween($start, $end));
        $this->assertTrue($start->isBetween($start, $end));
        $this->assertTrue($end->isBetween($start, $end));

        $this->assertTrue($middle->isBetweenExclusive($start, $end));
        $this->assertFalse($start->isBetweenExclusive($start, $end));
        $this->assertFalse($end->isBetweenExclusive($start, $end));
    }

    public function test_between_time_reversed_parameters(): void
    {
        $early = Timestamp::fromParts(1000, 0);
        $middle = Timestamp::fromParts(1500, 0);
        $late = Timestamp::fromParts(2000, 0);

        $this->assertTrue($middle->betweenTimeInclusive($late, $early));
        $this->assertTrue($middle->betweenTimeExclusive($late, $early));
        $this->assertTrue($middle->isBetween($late, $early));
        $this->assertTrue($middle->isBetweenExclusive($late, $early));
    }

    public function test_nano_precision_temporal_comparisons(): void
    {
        $base = Timestamp::fromParts(1234567890, 0);
        $plusOneNano = Timestamp::fromParts(1234567890, 1);
        $minusOneNano = Timestamp::fromParts(1234567889, 999999999);

        $this->assertTrue($base->isAfter($minusOneNano));
        $this->assertTrue($base->isBefore($plusOneNano));
        $this->assertTrue($plusOneNano->isAfter($base));
        $this->assertTrue($minusOneNano->isBefore($base));

        $this->assertTrue($base->isAfterOrAt($minusOneNano));
        $this->assertTrue($base->isBeforeOrAt($plusOneNano));
        $this->assertTrue($base->isAfterOrAt($base));
        $this->assertTrue($base->isBeforeOrAt($base));

        $this->assertFalse($base->isSameTime($plusOneNano));
        $this->assertFalse($base->isSameTime($minusOneNano));
        $this->assertTrue($base->isSameTime($base));
    }

    public function test_future_past_comprehensive(): void
    {
        $now = Timestamp::monotonic();
        $future = $now->plusMilliseconds(1);
        $past = $now->minusMilliseconds(1);

        $this->assertTrue($future->isFuture());
        $this->assertFalse($future->isPast());

        $this->assertTrue($past->isPast());
        $this->assertFalse($past->isFuture());

        $this->assertFalse($now->minusSecond()->isFuture());
        $this->assertFalse($now->plusSecond()->isPast());

        $veryFuture = $now->plusSeconds(3600);
        $veryPast = $now->minusSeconds(3600);

        $this->assertTrue($veryFuture->isFuture());
        $this->assertFalse($veryFuture->isPast());
        $this->assertTrue($veryPast->isPast());
        $this->assertFalse($veryPast->isFuture());
    }

    public function test_since_and_between_duration_methods(): void
    {
        $start = Timestamp::fromParts(1000, 500000000);
        $end = Timestamp::fromParts(1005, 750000000);

        $duration = $end->since($start);
        $this->assertSame(5, $duration->getSeconds());
        $this->assertSame(250000000, $duration->getNanoseconds());

        $reverseDuration = $start->since($end);
        $this->assertSame(-5, $reverseDuration->getSeconds());
        $this->assertSame(-250000000, $reverseDuration->getNanoseconds());

        $betweenDuration = $start->between($end);
        $this->assertSame(-5, $betweenDuration->getSeconds());
        $this->assertSame(-250000000, $betweenDuration->getNanoseconds());

        $sameDuration = $start->since($start);
        $this->assertSame(0, $sameDuration->getSeconds());
        $this->assertSame(0, $sameDuration->getNanoseconds());
    }

    public function test_temporal_comparison_with_large_values(): void
    {
        $large1 = Timestamp::fromParts(9223372036, 999999999);
        $large2 = Timestamp::fromParts(9223372036, 999999998);

        $this->assertTrue($large1->isAfter($large2));
        $this->assertFalse($large1->isBefore($large2));
        $this->assertTrue($large1->isAfterOrAt($large2));
        $this->assertFalse($large1->isBeforeOrAt($large2));
        $this->assertFalse($large1->isSameTime($large2));
    }

    public function test_temporal_comparison_edge_case_overflow_boundary(): void
    {
        $maxSeconds = Timestamp::fromParts(9223372036854775806, 0);
        $nearMax = Timestamp::fromParts(9223372036854775805, 999999999);

        $this->assertTrue($maxSeconds->isAfter($nearMax));
        $this->assertFalse($maxSeconds->isBefore($nearMax));
        $this->assertTrue($maxSeconds->isAfterOrAt($nearMax));
        $this->assertFalse($maxSeconds->isBeforeOrAt($nearMax));
        $this->assertFalse($maxSeconds->isSameTime($nearMax));
    }
}
