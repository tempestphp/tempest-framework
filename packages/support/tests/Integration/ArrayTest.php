<?php

declare(strict_types=1);

namespace Tempest\Support\Tests\Integration;

use PHPUnit\Framework\Attributes\DataProvider;
use Tempest\Support\Str\ImmutableString;
use Tempest\Support\Str\MutableString;
use Tempest\Support\Tests\Integration\Fixtures\TestObject;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

use function Tempest\Support\arr;

/**
 * @internal
 */
final class ArrayTest extends FrameworkIntegrationTestCase
{
    public function test_map_to(): void
    {
        $array = arr([['name' => 'test']])->mapTo(TestObject::class);

        $this->assertInstanceOf(TestObject::class, $array[0]);
    }

    public function test_map_first_to(): void
    {
        $array = arr([
            'foo',
            'bar',
        ]);

        $this->assertInstanceOf(ImmutableString::class, $array->mapFirstTo(ImmutableString::class));
        $this->assertInstanceOf(MutableString::class, $array->mapFirstTo(MutableString::class));
        $this->assertEquals('foo', $array->mapFirstTo(MutableString::class));

        $array = arr([
            ['name' => 'test'],
        ]);

        $this->assertInstanceOf(TestObject::class, $array->mapFirstTo(TestObject::class));
    }

    public function test_map_last_to(): void
    {
        $array = arr([
            'foo',
            'bar',
        ]);

        $this->assertInstanceOf(ImmutableString::class, $array->mapLastTo(ImmutableString::class));
        $this->assertInstanceOf(MutableString::class, $array->mapLastTo(MutableString::class));
        $this->assertEquals('bar', $array->mapLastTo(MutableString::class));

        $array = arr([
            ['name' => 'jon'],
            ['name' => 'doe'],
        ]);

        $this->assertInstanceOf(TestObject::class, $array->mapLastTo(TestObject::class));
    }

    #[DataProvider('provide_sort_cases')]
    public function test_sort(array $input, array $expected): void
    {
        $array = arr($input)->sort()->toArray();
        $this->assertEquals($expected, $array);
    }

    public static function provide_sort_cases(): iterable
    {
        return [
            'Regular case' => [[3, 1, 4, 1, 5, 9], [1, 1, 3, 4, 5, 9]],
            'Empty array' => [[], []],
            'Single element' => [[1], [1]],
            'All elements the same' => [[2, 2, 2], [2, 2, 2]],
            'Reverse order' => [[5, 4, 3, 2, 1], [1, 2, 3, 4, 5]],
            'Negative numbers' => [[-1, -3, -2, 0], [-3, -2, -1, 0]],
            'Mixed positive and negative' => [[3, -1, 4, 1, -5, 9], [-5, -1, 1, 3, 4, 9]],
            'Strings' => [
                ['apple', 'orange', 'banana'],
                ['apple', 'banana', 'orange'],
            ],
            'Large array' => [range(1000, 1, -1), range(1, 1000)],
        ];
    }
}
