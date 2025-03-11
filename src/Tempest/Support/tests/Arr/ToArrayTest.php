<?php

declare(strict_types=1);

namespace Tempest\Support\Tests\Arr;

use ArrayAccess;
use ArrayIterator;
use Countable;
use PHPUnit\Framework\TestCase;
use Tempest\Support\Str\ImmutableString;

use function Tempest\Support\Arr\to_array;

/**
 * @internal
 */
final class ToArrayTest extends TestCase
{
    public function test_array_input_returns_unchanged(): void
    {
        $input = ['a' => 1, 'b' => 2, 'c' => 3];

        $this->assertSame($input, to_array($input));
    }

    public function test_null_returns_empty_array(): void
    {
        $this->assertEquals([], to_array(null));
    }

    public function test_scalar_values_are_wrapped_in_array(): void
    {
        $this->assertEquals(['foo'], to_array(new ImmutableString('foo')));
        $this->assertEquals([42], to_array(42));
        $this->assertEquals(['test'], to_array('test'));
        $this->assertEquals([true], to_array(true));
        $this->assertEquals([3.14], to_array(3.14));
    }

    public function test_traversable_objects_are_converted_to_arrays(): void
    {
        $iterator = new ArrayIterator(['a' => 1, 'b' => 2, 'c' => 3]);
        $expected = ['a' => 1, 'b' => 2, 'c' => 3];

        $this->assertEquals($expected, to_array($iterator));
    }

    public function test_array_access_and_countable_objects_are_converted_to_arrays(): void
    {
        $object = new class() implements ArrayAccess, Countable {
            private $data = [
                0 => 'zero',
                1 => 'one',
                2 => 'two',
            ];

            public function offsetExists($offset): bool
            {
                return isset($this->data[$offset]);
            }

            public function offsetGet($offset): mixed
            {
                return $this->data[$offset] ?? null;
            }

            public function offsetSet($offset, $value): void
            {
                if (is_null($offset)) {
                    $this->data[] = $value;
                } else {
                    $this->data[$offset] = $value;
                }
            }

            public function offsetUnset($offset): void
            {
                unset($this->data[$offset]);
            }

            public function count(): int
            {
                return count($this->data);
            }
        };

        $this->assertEquals([0 => 'zero', 1 => 'one', 2 => 'two'], to_array($object));
    }

    public function test_array_access_without_traversable_or_countable(): void
    {
        $object = new class() implements ArrayAccess {
            private $data = [
                'key' => 'value',
            ];

            public function offsetExists($offset): bool
            {
                return isset($this->data[$offset]);
            }

            public function offsetGet($offset): mixed
            {
                return $this->data[$offset] ?? null;
            }

            public function offsetSet($offset, $value): void
            {
                if (is_null($offset)) {
                    $this->data[] = $value;
                } else {
                    $this->data[$offset] = $value;
                }
            }

            public function offsetUnset($offset): void
            {
                unset($this->data[$offset]);
            }
        };

        $this->assertEquals([$object], to_array($object));
    }

    public function test_regular_object_is_wrapped(): void
    {
        $object = new class() {
            public $foo = 'bar';

            public $baz = 42;
        };

        $this->assertEquals([$object], to_array($object));
    }
}
