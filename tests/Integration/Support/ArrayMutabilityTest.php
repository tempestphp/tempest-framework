<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Support;

use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use Tempest\Support\Arr\ImmutableArray;
use Tempest\Support\Arr\MutableArray;

/**
 * @internal
 */
final class ArrayMutabilityTest extends TestCase
{
    use GeneratesArguments;

    /**
     * These methods are either irrelevant to test, or their arguments make them difficult to generate values for.
     */
    private const array EXCLUDED_METHODS = [
        'combine',
        'each',
        'map',
        'mapWithKeys',
        'flatMap',
        'mapTo',
        'sortByCallback',
        'sortKeysByCallback',
        'dump',
        'dd',
        'tap',
    ];

    public function test_immutable_array(): void
    {
        $this->assertAllMethods(ImmutableArray::class, function (object $expected, object $actual, string $method): void {
            $this->assertNotSame($expected, $actual, "Method `{$method}` is not immutable.");
        }, except: self::EXCLUDED_METHODS);
    }

    public function test_mutable_array(): void
    {
        $this->assertAllMethods(MutableArray::class, function (object $expected, object $actual, string $method): void {
            $this->assertSame($expected, $actual, "Method `{$method}` is not mutable.");
        }, except: self::EXCLUDED_METHODS);
    }

    #[TestWith([MutableArray::class, ImmutableArray::class, 'toImmutableArray'])]
    #[TestWith([ImmutableArray::class, MutableArray::class, 'toMutableArray'])]
    public function test_convert_between_string_instances(string $initial, string $target, string $method): void
    {
        $instance = new $initial('foo');

        $this->assertInstanceOf($target, $instance->$method());
        $this->assertSame(['foo'], $instance->value);
    }
}
