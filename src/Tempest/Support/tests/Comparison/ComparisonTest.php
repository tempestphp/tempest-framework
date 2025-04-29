<?php

namespace Tempest\Support\Tests\Comparison;

use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Tempest\Support\Comparison;
use Tempest\Support\Comparison\Comparable;
use Tempest\Support\Comparison\Exception\IncomparableException;
use Tempest\Support\Comparison\Order;

final class ComparisonTest extends TestCase
{
    #[DataProvider('provideComparisonCases')]
    public function test_it_can_compare(mixed $a, mixed $b, Order $expected): void
    {
        $this->assertSame($expected, Comparison\compare($a, $b));
    }

    public function test_it_can_fail_comparing(): void
    {
        $a = static::createIncomparableWrapper(1);
        $b = static::createIncomparableWrapper(2);

        $this->expectException(Comparison\Exception\IncomparableException::class);
        $this->expectExceptionMessage('Unable to compare "int" with "int".');

        Comparison\compare($a, $b);
    }

    public function test_it_can_fail_comparing_with_additional_info(): void
    {
        $a = static::createIncomparableWrapper(1, 'Can only compare even numbers');
        $b = static::createIncomparableWrapper(2);

        $this->expectException(Comparison\Exception\IncomparableException::class);
        $this->expectExceptionMessage('Unable to compare "int" with "int": Can only compare even numbers');

        Comparison\compare($a, $b);
    }

    public static function provideComparisonCases(): Generator
    {
        yield 'scalar-default' => [0, 0, Order::default()];
        yield 'scalar-equal' => [0, 0, Order::EQUAL];
        yield 'scalar-less' => [0, 1, Order::LESS];
        yield 'scalar-greater' => [1, 0, Order::GREATER];

        yield 'comparable-default' => [
            static::createComparableIntWrapper(0),
            static::createComparableIntWrapper(0),
            Order::default(),
        ];
        yield 'comparable-equal' => [
            static::createComparableIntWrapper(0),
            static::createComparableIntWrapper(0),
            Order::EQUAL,
        ];
        yield 'comparable-less' => [
            static::createComparableIntWrapper(0),
            static::createComparableIntWrapper(1),
            Order::LESS,
        ];
        yield 'comparable-greater' => [
            static::createComparableIntWrapper(1),
            static::createComparableIntWrapper(0),
            Order::GREATER,
        ];
    }

    private static function createComparableIntWrapper(int $i): Comparable
    {
        return new class($i) implements Comparable {
            public function __construct(
                public readonly int $int,
            ) {}

            #[\Override]
            public function compare(mixed $other): Order
            {
                return Order::from($this->int <=> $other->int);
            }
        };
    }

    private static function createIncomparableWrapper(int $i, string $additionalInfo = ''): Comparable
    {
        return new class($i, $additionalInfo) implements Comparable {
            public function __construct(
                public readonly int $int,
                public readonly string $additionalInfo,
            ) {}

            #[\Override]
            public function compare(mixed $other): Order
            {
                throw IncomparableException::fromValues($this->int, $other->int, $this->additionalInfo);
            }
        };
    }
}
