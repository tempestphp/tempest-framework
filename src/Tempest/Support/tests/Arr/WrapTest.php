<?php

declare(strict_types=1);

namespace Tempest\Support\Tests\Arr;

use ArrayAccess;
use ArrayIterator;
use Countable;
use PHPUnit\Framework\TestCase;
use Tempest\Support\Str\ImmutableString;

use function Tempest\Support\Arr\wrap;

/**
 * @internal
 */
final class WrapTest extends TestCase
{
    public function test_array_input_returns_unchanged(): void
    {
        $input = ['a' => 1, 'b' => 2, 'c' => 3];

        $this->assertSame($input, wrap($input));
    }

    public function test_null_returns_empty_array(): void
    {
        $this->assertEquals([], wrap(null));
    }

    public function test_scalar_values_are_wrapped_in_array(): void
    {
        $foo = new ImmutableString('foo');
        $this->assertSame([$foo], wrap($foo));

        $this->assertEquals([42], wrap(42));
        $this->assertEquals(['test'], wrap('test'));
        $this->assertEquals([true], wrap(true));
        $this->assertEquals([3.14], wrap(3.14));
    }

    public function test_traversable_objects_are_not_converted_to_arrays(): void
    {
        $iterator = new ArrayIterator(['a' => 1, 'b' => 2, 'c' => 3]);

        $this->assertEquals([$iterator], wrap($iterator));
    }

    public function test_array_access_and_countable_objects_are_not_converted_to_arrays(): void
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

        $this->assertEquals([$object], wrap($object));
    }
}
