<?php

declare(strict_types=1);

namespace Tempest\Support\Tests\Arr;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Support\Arr\ImmutableArray;

/**
 * @internal
 */
final class ImmutableArrayTest extends TestCase
{
    #[Test]
    public function add(): void
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

    #[Test]
    public function add_diverse_values(): void
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
            expected: ['name'],
            actual: $collection->add('name')->toArray(),
        );
    }

    #[Test]
    public function null_values_are_accessible_by_offset(): void
    {
        $collection = new ImmutableArray(['key' => null]);

        $this->assertTrue(isset($collection['key']));
        $this->assertNull($collection['key']);
    }

    #[Test]
    public function remove_with_basic_keys(): void
    {
        $collection = new ImmutableArray([1, 2, 3]);

        $this->assertEquals(
            $collection->removeKeys(1)->toArray(),
            [0 => 1, 2 => 3],
        );

        $this->assertEquals(
            $collection->removeKeys([0, 2])->toArray(),
            [1 => 2],
        );
    }

    #[Test]
    public function remove_with_associative_keys(): void
    {
        $collection = new ImmutableArray([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'age' => 42,
        ]);

        $this->assertEquals(
            $collection->removeKeys('first_name')->toArray(),
            ['last_name' => 'Doe', 'age' => 42],
        );

        $this->assertEquals(
            $collection->removeKeys(['last_name', 'age'])->toArray(),
            ['first_name' => 'John'],
        );
    }

    #[Test]
    public function remove_values_with_basic_keys(): void
    {
        $collection = new ImmutableArray([1, 2, 3]);

        $this->assertEquals(
            $collection->removeValues(1)->toArray(),
            [1 => 2, 2 => 3],
        );

        $this->assertEquals(
            $collection->toArray(),
            [0 => 1, 1 => 2, 2 => 3],
        );

        $this->assertEquals(
            $collection->removeValues([0, 2])->toArray(),
            [0 => 1, 2 => 3],
        );
    }

    #[Test]
    public function remove_values_with_associative_keys(): void
    {
        $collection = new ImmutableArray([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'age' => 42,
        ]);

        $this->assertEquals(
            $collection->removeValues('John')->toArray(),
            ['last_name' => 'Doe', 'age' => 42],
        );

        $this->assertEquals($collection->count(), 3);

        $this->assertEquals(
            $collection->removeValues(['Doe', 42])->toArray(),
            ['first_name' => 'John'],
        );
    }
}
