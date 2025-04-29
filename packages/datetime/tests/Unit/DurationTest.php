<?php

declare(strict_types=1);

namespace Tempest\DateTime\Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use Tempest\DateTime;
use Tempest\Support\Comparison\Order;
use Tempest\Support\Json;

use function serialize;
use function Tempest\Support\Json;
use function unserialize;

/**
 * @mago-expect php-unit/strict-assertions
 */
final class DurationTest extends TestCase
{
    use DateTimeTestTrait;

    public function test_getters(): void
    {
        $t = DateTime\Duration::fromParts(1, 2, 3, 4);

        $this->assertSame(1, $t->getHours());
        $this->assertSame(2, $t->getMinutes());
        $this->assertSame(3, $t->getSeconds());
        $this->assertSame(4, $t->getNanoseconds());
        $this->assertSame([1, 2, 3, 4], $t->getParts());
    }

    public function test_named_constructors(): void
    {
        $this->assertSame(168.0, DateTime\Duration::weeks(1)->getTotalHours());
        $this->assertSame(24.0, DateTime\Duration::days(1)->getTotalHours());
        $this->assertSame(1.0, DateTime\Duration::hours(1)->getTotalHours());
        $this->assertSame(1.0, DateTime\Duration::minutes(1)->getTotalMinutes());
        $this->assertSame(1.0, DateTime\Duration::seconds(1)->getTotalSeconds());
        $this->assertSame(1.0, DateTime\Duration::milliseconds(1)->getTotalMilliseconds());
        $this->assertSame(1.0, DateTime\Duration::microseconds(1)->getTotalMicroseconds());
        $this->assertSame(1, DateTime\Duration::nanoseconds(1)->getNanoseconds());
        $this->assertSame(0.0, DateTime\Duration::zero()->getTotalSeconds());
    }

    #[TestWith([0, 0, 0, 0, 0.0])]
    #[TestWith([0, 0, 0, 1, 2.777777777777778E-13])]
    #[TestWith([1, 0, 0, 0, 1.0])]
    #[TestWith([1, 30, 0, 0, 1.5])]
    #[TestWith([2, 15, 30, 0, 2.2583333333333333])]
    #[TestWith([-1, 0, 0, 0, -1.0])]
    #[TestWith([-1, -30, 0, 0, -1.5])]
    #[TestWith([-2, -15, -30, 0, -2.2583333333333333])]
    public function test_get_total_hours(
        int $hours,
        int $minutes,
        int $seconds,
        int $nanoseconds,
        float $expectedHours,
    ): void {
        $time = DateTime\Duration::fromParts($hours, $minutes, $seconds, $nanoseconds);
        $this->assertSame($expectedHours, $time->getTotalHours());
    }

    #[TestWith([0, 0, 0, 0, 0.0])]
    #[TestWith([0, 0, 0, 1, 1.6666666666666667E-11])]
    #[TestWith([1, 0, 0, 0, 60.0])]
    #[TestWith([1, 30, 0, 0, 90.0])]
    #[TestWith([2, 15, 30, 0, 135.5])]
    #[TestWith([-1, 0, 0, 0, -60.0])]
    #[TestWith([-1, -30, 0, 0, -90.0])]
    #[TestWith([-2, -15, -30, 0, -135.5])]
    public function test_get_total_minutes(int $hours, int $minutes, int $seconds, int $nanoseconds, float $expectedMinutes): void
    {
        $time = DateTime\Duration::fromParts($hours, $minutes, $seconds, $nanoseconds);
        $this->assertSame($expectedMinutes, $time->getTotalMinutes());
    }

    #[TestWith([0, 0, 0, 0, 0.0])]
    #[TestWith([0, 0, 0, 1, 0.000000001])]
    #[TestWith([1, 0, 0, 0, 3600.0])]
    #[TestWith([1, 30, 0, 0, 5400.0])]
    #[TestWith([2, 15, 30, 0, 8130.0])]
    #[TestWith([-1, 0, 0, 0, -3600.0])]
    #[TestWith([-1, -30, 0, 0, -5400.0])]
    #[TestWith([-2, -15, -30, 0, -8130.0])]
    public function test_get_total_seconds(int $hours, int $minutes, int $seconds, int $nanoseconds, float $expectedSeconds): void
    {
        $time = DateTime\Duration::fromParts($hours, $minutes, $seconds, $nanoseconds);
        $this->assertSame($expectedSeconds, $time->getTotalSeconds());
    }

    #[TestWith([0, 0, 0, 0, 0.0])]
    #[TestWith([0, 0, 0, 1, 0.000001])]
    #[TestWith([1, 0, 0, 0, 3600000.0])]
    #[TestWith([1, 30, 0, 0, 5400000.0])]
    #[TestWith([2, 15, 30, 0, 8130000.0])]
    #[TestWith([-1, 0, 0, 0, -3600000.0])]
    #[TestWith([-1, -30, 0, 0, -5400000.0])]
    #[TestWith([-2, -15, -30, 0, -8130000.0])]
    public function test_get_total_milliseconds(int $hours, int $minutes, int $seconds, int $nanoseconds, float $expectedMilliseconds): void
    {
        $time = DateTime\Duration::fromParts($hours, $minutes, $seconds, $nanoseconds);
        $this->assertSame($expectedMilliseconds, $time->getTotalMilliseconds());
    }

    #[TestWith([0, 0, 0, 0, 0.0])]
    #[TestWith([0, 0, 0, 1, 0.001])]
    #[TestWith([1, 0, 0, 0, 3600000000.0])]
    #[TestWith([1, 30, 0, 0, 5400000000.0])]
    #[TestWith([2, 15, 30, 0, 8130000000.0])]
    #[TestWith([-1, 0, 0, 0, -3600000000.0])]
    #[TestWith([-1, -30, 0, 0, -5400000000.0])]
    #[TestWith([-2, -15, -30, 0, -8130000000.0])]
    public function test_get_total_microseconds(int $hours, int $minutes, int $seconds, int $nanoseconds, float $expectedMicroseconds): void
    {
        $time = DateTime\Duration::fromParts($hours, $minutes, $seconds, $nanoseconds);
        $this->assertSame($expectedMicroseconds, $time->getTotalMicroseconds());
    }

    public function test_setters(): void
    {
        $t = DateTime\Duration::fromParts(1, 2, 3, 4);

        $this->assertSame([42, 2, 3, 4], $t->withHours(42)->getParts());
        $this->assertSame([1, 42, 3, 4], $t->withMinutes(42)->getParts());
        $this->assertSame([1, 2, 42, 4], $t->withSeconds(42)->getParts());
        $this->assertSame([1, 2, 3, 42], $t->withNanoseconds(42)->getParts());
        $this->assertSame([2, 3, 3, 4], $t->withMinutes(63)->getParts());
        $this->assertSame([1, 3, 3, 4], $t->withSeconds(63)->getParts());
        $this->assertSame([1, 2, 4, 42], $t->withNanoseconds(DateTime\NANOSECONDS_PER_SECOND + 42)->getParts());
        $this->assertSame([1, 2, 3, 4], $t->getParts());
    }

    public function test_fractions_of_second(): void
    {
        $this->assertSame([0, 0, 0, 0], DateTime\Duration::zero()->getParts());
        $this->assertSame([0, 0, 0, 42], DateTime\Duration::nanoseconds(42)->getParts());
        $this->assertSame(
            [0, 0, 1, 42],
            DateTime\Duration::nanoseconds(DateTime\NANOSECONDS_PER_SECOND + 42)->getParts(),
        );
        $this->assertSame([0, 0, 0, 42000], DateTime\Duration::microseconds(42)->getParts());
        $this->assertSame([0, 0, 1, 42000], DateTime\Duration::microseconds(1000042)->getParts());
        $this->assertSame([0, 0, 0, 42000000], DateTime\Duration::milliseconds(42)->getParts());
        $this->assertSame([0, 0, 1, 42000000], DateTime\Duration::milliseconds(1042)->getParts());
    }

    #[TestWith([0, 0, 0, 0])]
    #[TestWith([0, 3, 0, 3])]
    #[TestWith([3, 0, 3, 0])]
    #[TestWith([1, 3, 1, 3])]
    #[TestWith([1, -3, 0, DateTime\NANOSECONDS_PER_SECOND - 3])]
    #[TestWith([-1, 3, 0, -(DateTime\NANOSECONDS_PER_SECOND - 3)])]
    #[TestWith([-1, -3, -1, -3])]
    #[TestWith([1, DateTime\NANOSECONDS_PER_SECOND + 42, 2, 42])]
    #[TestWith([1, -(DateTime\NANOSECONDS_PER_SECOND + 42), 0, -42])]
    #[TestWith([2, -3, 1, DateTime\NANOSECONDS_PER_SECOND - 3])]
    public function test_normalized(int $input_s, int $input_ns, int $normalized_s, int $normalized_ns): void
    {
        $this->assertSame(
            [0, 0, $normalized_s, $normalized_ns],
            DateTime\Duration::fromParts(0, 0, $input_s, $input_ns)->getParts(),
        );
    }

    public function test_normalized_hms(): void
    {
        $this->assertSame([3, 5, 4, 0], DateTime\Duration::fromParts(2, 63, 124)->getParts());
        $this->assertSame([0, 59, 4, 0], DateTime\Duration::fromParts(2, -63, 124)->getParts());
        $this->assertSame(
            [-1, 0, -55, -(DateTime\NANOSECONDS_PER_SECOND - 42)],
            DateTime\Duration::fromParts(0, -63, 124, 42)->getParts(),
        );
        $this->assertSame([42, 0, 0, 0], DateTime\Duration::hours(42)->getParts());
        $this->assertSame([1, 3, 0, 0], DateTime\Duration::minutes(63)->getParts());
        $this->assertSame([0, -1, -3, 0], DateTime\Duration::seconds(-63)->getParts());
        $this->assertSame([0, 0, -1, 0], DateTime\Duration::nanoseconds(-DateTime\NANOSECONDS_PER_SECOND)->getParts());
    }

    #[TestWith([0, 0, 0, 0, 0])]
    #[TestWith([0, 42, 0, 0, 1])]
    #[TestWith([0, 0, -42, 0, -1])]
    #[TestWith([1, -63, 0, 0, -1])]
    public function test_positive_negative(int $h, int $m, int $s, int $ns, int $expected_sign): void
    {
        $t = DateTime\Duration::fromParts($h, $m, $s, $ns);
        $this->assertSame($expected_sign === 0, $t->isZero());
        $this->assertSame($expected_sign === 1, $t->isPositive());
        $this->assertSame($expected_sign === -1, $t->isNegative());
    }

    /**
     * @return list<array{DateTime\Duration, DateTime\Duration, Order}>
     */
    public static function provide_compare_data(): array
    {
        return [
            [DateTime\Duration::seconds(20), DateTime\Duration::seconds(10), Order::GREATER],
            [DateTime\Duration::seconds(10), DateTime\Duration::seconds(20), Order::LESS],
            [DateTime\Duration::seconds(10), DateTime\Duration::seconds(10), Order::EQUAL],
            [DateTime\Duration::hours(1), DateTime\Duration::minutes(42), Order::GREATER],
            [DateTime\Duration::minutes(2), DateTime\Duration::seconds(120), Order::EQUAL],
            [DateTime\Duration::zero(), DateTime\Duration::nanoseconds(1), Order::LESS],
        ];
    }

    #[DataProvider('provide_compare_data')]
    public function test_compare(DateTime\Duration $a, DateTime\Duration $b, Order $expected): void
    {
        $opposite = Order::from(-$expected->value);

        $this->assertSame($expected, $a->compare($b));
        $this->assertSame($opposite, $b->compare($a));
        $this->assertSame($expected === Order::EQUAL, $a->equals($b));
        $this->assertSame($expected === Order::LESS, $a->shorter($b));
        $this->assertSame($expected !== Order::GREATER, $a->shorterOrEqual($b));
        $this->assertSame($expected === Order::GREATER, $a->longer($b));
        $this->assertSame($expected !== Order::LESS, $a->longerOrEqual($b));
        $this->assertFalse($a->betweenExclusive($a, $a));
        $this->assertFalse($a->betweenExclusive($a, $b));
        $this->assertFalse($a->betweenExclusive($b, $a));
        $this->assertFalse($a->betweenExclusive($b, $b));
        $this->assertTrue($a->betweenInclusive($a, $a));
        $this->assertTrue($a->betweenInclusive($a, $b));
        $this->assertTrue($a->betweenInclusive($b, $a));
        $this->assertSame($expected === Order::EQUAL, $a->betweenInclusive($b, $b));
    }

    public function test_is_between(): void
    {
        $a = DateTime\Duration::hours(1);
        $b = DateTime\Duration::minutes(64);
        $c = DateTime\Duration::fromParts(1, 30);
        $this->assertTrue($b->betweenExclusive($a, $c));
        $this->assertTrue($b->betweenExclusive($c, $a));
        $this->assertTrue($b->betweenInclusive($a, $c));
        $this->assertTrue($b->betweenInclusive($c, $a));
        $this->assertFalse($a->betweenExclusive($b, $c));
        $this->assertFalse($a->betweenInclusive($c, $b));
        $this->assertFalse($c->betweenInclusive($a, $b));
        $this->assertFalse($c->betweenExclusive($b, $a));
    }

    public function test_operations(): void
    {
        $z = DateTime\Duration::zero();
        $a = DateTime\Duration::fromParts(0, 2, 25);
        $b = DateTime\Duration::fromParts(0, 0, -63, 42);
        $this->assertSame([0, 0, 0, 0], $z->invert()->getParts());
        $this->assertSame([0, -2, -25, 0], $a->invert()->getParts());
        $this->assertSame([0, 1, 2, DateTime\NANOSECONDS_PER_SECOND - 42], $b->invert()->getParts());
        $this->assertSame($a->getParts(), $z->plus($a)->getParts());
        $this->assertSame($b->getParts(), $b->plus($z)->getParts());
        $this->assertSame($b->invert()->getParts(), $z->minus($b)->getParts());
        $this->assertSame($a->getParts(), $a->minus($z)->getParts());
        $this->assertSame([0, 1, 22, 42], $a->plus($b)->getParts());
        $this->assertSame([0, 1, 22, 42], $b->plus($a)->getParts());
        $this->assertSame([0, 3, 27, DateTime\NANOSECONDS_PER_SECOND - 42], $a->minus($b)->getParts());
        $this->assertSame([0, -3, -27, -(DateTime\NANOSECONDS_PER_SECOND - 42)], $b->minus($a)->getParts());
        $this->assertSame($b->invert()->plus($a)->getParts(), $a->minus($b)->getParts());
    }

    #[TestWith([42, 0, 0, 0, '42 hour(s)'])]
    #[TestWith([0, 42, 0, 0, '42 minute(s)'])]
    #[TestWith([0, 0, 42, 0, '42 second(s)'])]
    #[TestWith([0, 0, 0, 0, '0 second(s)'])]
    #[TestWith([0, 0, 0, 42, '0 second(s)'])]
    #[TestWith([0, 0, 1, 42, '1 second(s)'])]
    #[TestWith([0, 0, 1, 20000000, '1.02 second(s)'])]
    #[TestWith([1, 2, 0, 0, '1 hour(s), 2 minute(s)'])]
    #[TestWith([1, 0, 3, 0, '1 hour(s), 0 minute(s), 3 second(s)'])]
    #[TestWith([0, 2, 3, 0, '2 minute(s), 3 second(s)'])]
    #[TestWith([1, 2, 3, 0, '1 hour(s), 2 minute(s), 3 second(s)'])]
    #[TestWith([1, 0, 0, 42000000, '1 hour(s), 0 minute(s), 0.042 second(s)'])]
    #[TestWith([-42, 0, -42, 0, '-42 hour(s), 0 minute(s), -42 second(s)'])]
    #[TestWith([-42, 0, -42, -420000000, '-42 hour(s), 0 minute(s), -42.42 second(s)'])]
    #[TestWith([0, 0, 0, -420000000, '-0.42 second(s)'])]
    public function test_to_string(int $h, int $m, int $s, int $ns, string $expected): void
    {
        $this->assertSame($expected, DateTime\Duration::fromParts($h, $m, $s, $ns)->toString());
    }

    public function test_serialization(): void
    {
        $timeInterval = DateTime\Duration::fromParts(1, 30, 45, 500000000);
        $serialized = serialize($timeInterval);
        $deserialized = unserialize($serialized);

        $this->assertEquals($timeInterval, $deserialized);
    }

    public function test_json_encoding(): void
    {
        $timeInterval = DateTime\Duration::fromParts(1, 30, 45, 500000000);
        $jsonEncoded = Json\encode($timeInterval);
        $jsonDecoded = Json\decode($jsonEncoded, associative: true);

        $this->assertSame(['hours' => 1, 'minutes' => 30, 'seconds' => 45, 'nanoseconds' => 500000000], $jsonDecoded);
    }
}
