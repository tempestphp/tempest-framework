<?php

declare(strict_types=1);

namespace Tempest\Support\Tests;

use PHPUnit\Framework\TestCase;
use function Tempest\Support\arr;
use Tempest\Support\ArrayHelper;
use Tempest\Support\InvalidMapWithKeysUsage;

/**
 * @internal
 */
final class ArrayHelperTest extends TestCase
{
    public function test_wrap(): void
    {
        $this->assertTrue(arr()->equals([]));
        $this->assertTrue(arr('a')->equals(['a']));
        $this->assertTrue(arr(arr('a'))->equals(['a']));
        $this->assertTrue(arr(['a'])->equals(['a']));
    }

    public function test_to_array(): void
    {
        $this->assertSame(['a'], arr('a')->toArray());
    }

    public function test_loop(): void
    {
        $i = 0;

        foreach (arr(['a', 'b']) as $value) {
            $i++;
        }

        $this->assertSame(2, $i);
    }

    public function test_count(): void
    {
        $this->assertSame(2, arr(['a', 'b'])->count());
    }

    public function test_serialize(): void
    {
        $array = ['a', 'b'];

        $this->assertTrue(arr($array)->equals(unserialize(serialize($array))));
    }

    public function test_array_access(): void
    {
        $array = arr(['a' => 1, 'b' => 2]);

        $this->assertSame(1, $array['a']);
        $this->assertSame(2, $array['b']);
        $this->assertTrue(isset($array['a']));
        $this->assertFalse(isset($array['x']));

        unset($array['a']);
        $this->assertFalse(isset($array['a']));
    }

    public function test_arr_get(): void
    {
        $array = [
            'a' => [
                'b' => 'c',
            ],
        ];

        $this->assertSame('c', arr($array)->get('a.b'));
        $this->assertInstanceOf(ArrayHelper::class, arr($array)->get('a'));
        $this->assertNull(arr($array)->get('a.x'));
        $this->assertSame('default', arr($array)->get('a.x', 'default'));
    }

    public function test_arr_has(): void
    {
        $array = [
            'a' => [
                'b' => 'c',
            ],
        ];

        $this->assertTrue(arr($array)->has('a.b'));
        $this->assertTrue(arr($array)->has('a'));
        $this->assertFalse(arr($array)->has('a.x'));
    }

    public function test_arr_set(): void
    {
        $array = [
            'a' => [
                'b' => [
                    'c' => 'c',
                ],
            ],
        ];

        $this->assertTrue(arr()->set('a.b.c', 'c')->equals($array));
        $this->assertTrue(arr($array)->set('a', 'c')->equals(['a' => 'c']));
    }

    public function test_unwrap(): void
    {
        $expected = [
            'a' => [
                'b' => [
                    'c' => 'c',
                ],
            ],
        ];

        $input = [
            'a.b.c' => 'c',
        ];

        $this->assertTrue(arr($input)->unwrap()->equals($expected));
    }

    public function test_implode(): void
    {
        $this->assertSame('a,b,c', arr(['a', 'b', 'c'])->implode(','));
    }

    public function test_pop(): void
    {
        $array = arr(['a', 'b', 'c'])->pop($value);

        $this->assertSame('c', $value);
        $this->assertTrue($array->equals(['a', 'b']));
    }

    public function test_unshift(): void
    {
        $array = arr(['a', 'b', 'c'])->unshift($value);

        $this->assertSame('a', $value);
        $this->assertTrue($array->equals(['b', 'c']));
    }

    public function test_last(): void
    {
        $this->assertSame('c', arr(['a', 'b', 'c'])->last());
    }

    public function test_first(): void
    {
        $this->assertSame('a', arr(['a', 'b', 'c'])->first());
    }

    public function test_is_empty(): void
    {
        $this->assertTrue(arr()->isEmpty());
        $this->assertFalse(arr(['a'])->isEmpty());
    }

    public function test_map(): void
    {
        $this->assertTrue(
            arr(['a', 'b'])
                ->map(fn (string $value) => $value . 'x')
                ->equals(['ax', 'bx']),
        );

        $this->assertTrue(
            arr(['a', 'b'])
                ->map(fn (string $value, mixed $key) => $value . $key)
                ->equals(['a0', 'b1']),
        );
    }

    public function test_map_with_keys(): void
    {
        $this->assertTrue(
            arr(['a', 'b'])
                ->mapWithKeys(fn (mixed $value, mixed $key) => yield $value => $value)
                ->equals(['a' => 'a', 'b' => 'b']),
        );

        $this->assertTrue(
            arr(['a' => 'a', 'b' => 'b'])
                ->mapWithKeys(fn (mixed $value, mixed $key) => yield $value)
                ->equals(['b']),
        );
    }

    public function test_map_with_keys_without_generator(): void
    {
        $this->expectException(InvalidMapWithKeysUsage::class);

        arr(['a', 'b'])
            ->mapWithKeys(fn (mixed $value, mixed $key) => $value);
    }

    public function test_values(): void
    {
        $this->assertTrue(
            arr(['a' => 'a', 'b' => 'b'])
                ->values()
                ->equals(['a', 'b']),
        );
    }

    public function test_filter(): void
    {
        $this->assertTrue(
            arr(['a', 'b', 'c'])
                ->filter(fn (mixed $value) => $value === 'b')
                ->values()
                ->equals(['b']),
        );

        $this->assertTrue(
            arr(['a', 'b', 'c'])
                ->filter(fn (mixed $value, mixed $key) => $key === 1)
                ->values()
                ->equals(['b']),
        );
    }

    public function test_reverse(): void
    {
        $this->assertTrue(
            arr(['a', 'b', 'c'])
                ->reverse()
                ->equals(['c', 'b', 'a']),
        );
    }

    public function test_each(): void
    {
        $string = '';

        arr(['a', 'b', 'c'])->each(function (mixed $value) use (&$string): void {
            $string .= $value;
        });

        $this->assertSame('abc', $string);

        $string = '';

        arr(['a', 'b', 'c'])->each(function (mixed $value, mixed $key) use (&$string): void {
            $string .= $key;
        });

        $this->assertSame('012', $string);
    }

    public function test_contains(): void
    {
        $this->assertTrue(arr(['a', 'b', 'c'])->contains('b'));
        $this->assertFalse(arr(['a', 'b', 'c'])->contains('d'));
    }
}
