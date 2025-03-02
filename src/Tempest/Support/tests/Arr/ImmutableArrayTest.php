<?php

declare(strict_types=1);

namespace Tempest\Support\Tests\Arr;

use PHPUnit\Framework\TestCase;
use Tempest\Support\Arr\ImmutableArray;

/**
 * @internal
 */
final class ImmutableArrayTest extends TestCase
{
    public function test_add(): void
    {
        $collection = new ImmutableArray('a');

        $this->assertSame(
            $collection->add('b')->toArray(),
            ['a', 'b'],
        );

        $this->assertSame(
            $collection->add('b')->add('c')->toArray(),
            ['a', 'b', 'c'],
        );
    }

    public function test_add_diverse_values(): void
    {
        $collection = new ImmutableArray();

        $this->assertSame(
            $collection->add(1)->toArray(),
            [1],
        );

        $this->assertSame(
            $collection->add(2)->toArray(),
            [2],
        );

        $this->assertSame(
            $collection->add('')->toArray(),
            [''],
        );

        $this->assertSame(
            $collection->add(null)->toArray(),
            [null],
        );

        $this->assertSame(
            $collection->add(false)->toArray(),
            [false],
        );

        $this->assertSame(
            $collection->add([])->toArray(),
            [[]],
        );

        $this->assertSame(
            actual: $collection->add('name')->toArray(),
            expected: ['name'],
        );
    }

    public function test_remove_with_basic_keys(): void
    {
        $collection = new ImmutableArray([1, 2, 3]);

        $this->assertEquals(
            $collection->remove(1)->toArray(),
            [0 => 1, 2 => 3],
        );

        $this->assertEquals(
            $collection->remove([0, 2])->toArray(),
            [1 => 2],
        );
    }

    public function test_remove_with_associative_keys(): void
    {
        $collection = new ImmutableArray([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'age' => 42,
        ]);

        $this->assertEquals(
            $collection->remove('first_name')->toArray(),
            ['last_name' => 'Doe', 'age' => 42],
        );

        $this->assertEquals(
            $collection->remove(['last_name', 'age'])->toArray(),
            ['first_name' => 'John'],
        );
    }
}
