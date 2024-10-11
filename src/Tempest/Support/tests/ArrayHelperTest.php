<?php

declare(strict_types=1);

namespace Tempest\Support\Tests;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use function Tempest\Support\arr;
use Tempest\Support\ArrayHelper;
use Tempest\Support\InvalidMapWithKeysUsage;
use function Tempest\Support\str;

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

    public function test_arr_put_is_alias_of_set(): void
    {
        $array = [
            'a' => [
                'b' => [
                    'c' => 'c',
                ],
            ],
        ];

        $this->assertTrue(arr()->set('a.b.c', 'c')->equals($array));
        $this->assertTrue(arr()->put('a.b.c', 'c')->equals($array));
        $this->assertTrue(arr($array)->set('a', 'c')->equals(['a' => 'c']));
        $this->assertTrue(arr($array)->put('a', 'c')->equals(['a' => 'c']));
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
        $this->assertSame(
            ['a', 'b', '-1', -1, '0', 0],
            arr(['a', false, 'b', '-1', null, -1, '0', 0])
                ->filter()
                ->values()
                ->toArray(),
        );

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

    public function test_explode(): void
    {
        $this->assertEquals(['john', 'doe'], ArrayHelper::explode('john doe')->toArray());
        $this->assertEquals(['john', 'doe'], ArrayHelper::explode(str('john doe'))->toArray());
        $this->assertEquals(['john doe'], ArrayHelper::explode('john doe', ',')->toArray());
        $this->assertEquals(['john', 'doe'], ArrayHelper::explode('john, doe', ', ')->toArray());
        $this->assertEquals(['john, doe'], ArrayHelper::explode('john, doe', '')->toArray());
    }

    public function test_combine_with_integers(): void
    {
        $collection = arr([1, 2, 3]);
        $current = $collection
            ->combine([4, 5, 6])
            ->toArray();
        $expected = [
            1 => 4,
            2 => 5,
            3 => 6,
        ];

        $this->assertSame($expected, $current);
    }

    public function test_combine_with_strings(): void
    {
        $collection = arr([
            'first_name',
            'last_name',
        ]);
        $current = $collection
            ->combine([
                'John',
                'Doe',
            ])
            ->toArray();
        $expected = [
            'first_name' => 'John',
            'last_name' => 'Doe',
        ];

        $this->assertSame($expected, $current);
    }

    public function test_combine_with_associative_arrays(): void
    {
        $collection = arr([
            5 => 'first_name',
            'test' => 'last_name',
            42 => 'age',
        ]);
        $current = $collection
            ->combine([
                4 => 'John',
                5 => 'Doe',
                6 => 50,
            ])
            ->toArray();
        $expected = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'age' => 50,
        ];

        $this->assertSame($expected, $current);
    }

    public function test_combine_with_collection(): void
    {
        $collection = arr(['first_name', 'last_name']);
        $another_collection = arr(['John', 'Doe']);
        $current = $collection
            ->combine($another_collection)
            ->toArray();
        $expected = [
            'first_name' => 'John',
            'last_name' => 'Doe',
        ];

        $this->assertSame($expected, $current);
    }

    public function test_keys(): void
    {
        $collection = arr([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'framework' => 'Tempest',
        ]);
        $current = $collection
            ->keys()
            ->toArray();
        $expected = [
            'first_name',
            'last_name',
            'framework',
        ];

        $this->assertSame($expected, $current);
    }

    public function test_merge_array(): void
    {
        $collection = arr([
            'first_name' => 'John',
            'last_name' => 'Doe',
        ]);
        $current = $collection
            ->merge([
                'framework' => 'Tempest',
            ])
            ->toArray();
        $expected = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'framework' => 'Tempest',
        ];

        $this->assertSame($expected, $current);
    }

    public function test_merge_collection(): void
    {
        $collection = arr([
            'first_name' => 'John',
            'last_name' => 'Doe',
        ]);
        $current = $collection
            ->merge(arr([
                'framework' => 'Tempest',
            ]))
            ->toArray();
        $expected = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'framework' => 'Tempest',
        ];

        $this->assertSame($expected, $current);
    }

    public function test_diff_values(): void
    {
        $collection = arr([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'age' => 42,
        ]);
        $current = $collection
            ->diff([
                'John',
                'Doe',
            ])
            ->toArray();
        $expected = [
            'age' => 42,
        ];

        $this->assertSame($expected, $current);
    }

    public function test_diff_keys(): void
    {
        $collection = arr([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'age' => 42,
        ]);
        $current = $collection
            ->diffKeys([
                'age' => 10,
            ])
            ->toArray();
        $expected = [
            'first_name' => 'John',
            'last_name' => 'Doe',
        ];

        $this->assertSame($expected, $current);
    }

    public function test_intersect(): void
    {
        $collection = arr([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'age' => 42,
        ]);
        $current = $collection
            ->intersect([
                'John',
                'Doe',
            ])
            ->toArray();
        $expected = [
            'first_name' => 'John',
            'last_name' => 'Doe',
        ];

        $this->assertSame($expected, $current);
    }

    public function test_intersect_keys(): void
    {
        $collection = arr([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'age' => 42,
        ]);
        $current = $collection
            ->intersectKeys([
                'first_name' => true,
                'last_name' => true,
            ])
            ->toArray();
        $expected = [
            'first_name' => 'John',
            'last_name' => 'Doe',
        ];

        $this->assertSame($expected, $current);
    }

    public function test_unique_with_basic_item(): void
    {
        $collection = arr([
            'John',
            'Doe',
            'John',
            'Doe',
            'Jane',
            'Doe',
        ]);
        $current = $collection
            ->unique()
            ->values()
            ->toArray();
        $expected = [
            'John',
            'Doe',
            'Jane',
        ];

        $this->assertSame($expected, $current);
    }

    public function test_unique_with_associative_array(): void
    {
        $collection = arr([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'age' => 42,
            'steal_name' => 'John',
        ]);
        $current = $collection
            ->unique()
            ->toArray();
        $expected = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'age' => 42,
        ];

        $this->assertSame($expected, $current);
    }

    public function test_unique_with_arrays(): void
    {
        $collection = arr([
            ['John', 'Doe'],
            ['John', 'Doe'],
            [1, 2],
            [1, 2],
            [3, 4],
        ]);
        $current = $collection
            ->unique()
            ->values()
            ->toArray();
        $expected = [
            ['John', 'Doe'],
            [1, 2],
            [3, 4],
        ];

        $this->assertSame($expected, $current);
    }

    public function test_unique_with_key(): void
    {
        $collection = arr([
            ['id' => 1, 'first_name' => 'John', 'last_name' => 'Doe'],
            ['id' => 2, 'first_name' => 'John', 'last_name' => 'Doe'],
            ['id' => 3, 'first_name' => 'Jane', 'last_name' => 'Doe'],
            ['id' => 3, 'first_name' => 'Jane', 'last_name' => 'Duplicate'],
        ]);

        $this->assertSame(
            actual: $collection
                ->unique('first_name')
                ->values()
                ->toArray(),
            expected: [
                ['id' => 1, 'first_name' => 'John', 'last_name' => 'Doe'],
                ['id' => 3, 'first_name' => 'Jane', 'last_name' => 'Doe'],
            ],
        );

        $this->assertSame(
            actual: $collection
                ->unique('last_name')
                ->values()
                ->toArray(),
            expected: [
                ['id' => 1, 'first_name' => 'John', 'last_name' => 'Doe'],
                ['id' => 3, 'first_name' => 'Jane', 'last_name' => 'Duplicate'],
            ],
        );

        $this->assertSame(
            actual: $collection
                ->unique('id')
                ->values()
                ->toArray(),
            expected: [
                ['id' => 1, 'first_name' => 'John', 'last_name' => 'Doe'],
                ['id' => 2, 'first_name' => 'John', 'last_name' => 'Doe'],
                ['id' => 3, 'first_name' => 'Jane', 'last_name' => 'Doe'],
            ],
        );
    }

    public function test_unique_ensure_unnested_value_is_rejected_when_key_is_set(): void
    {
        $collection = arr([
            42,
            'Hello World',
            ['id' => 1, 'first_name' => 'John', 'last_name' => 'Doe'],
            ['id' => 2, 'first_name' => 'John', 'last_name' => 'Doe'],
            ['id' => 3, 'first_name' => 'Jane', 'last_name' => 'Doe'],
            ['id' => 3, 'first_name' => 'Jane', 'last_name' => 'Duplicate'],
        ]);

        $this->assertSame(
            actual: $collection
                ->unique('id')
                ->values()
                ->toArray(),
            expected: [
                ['id' => 1, 'first_name' => 'John', 'last_name' => 'Doe'],
                ['id' => 2, 'first_name' => 'John', 'last_name' => 'Doe'],
                ['id' => 3, 'first_name' => 'Jane', 'last_name' => 'Doe'],
            ],
        );
    }

    public function test_unique_unstrict_check(): void
    {
        $this->assertSame(
            actual: arr([
                42,
                '42',
                true,
                'true',
            ])
                ->unique(should_be_strict: false)
                ->values()
                ->toArray(),
            expected: [
                42,
                'true',
            ],
        );
    }

    public function test_unique_strict_check(): void
    {
        $this->assertSame(
            actual: arr([
                42,
                '42',
                true,
                'true',
            ])
                ->unique(should_be_strict: true)
                ->values()
                ->toArray(),
            expected: [
                42,
                '42',
                true,
                'true',
            ],
        );
    }

    public function test_unique_with_key_and_strict_check(): void
    {
        $this->assertSame(
            actual: arr([
                ['id' => 1, 'first_name' => 'John', 'last_name' => 'Doe'],
                ['id' => '1', 'first_name' => 'John', 'last_name' => 'Doe'],
                ['id' => 2, 'first_name' => 'John', 'last_name' => 'Doe'],
                ['id' => 3, 'first_name' => 'Jane', 'last_name' => 'Doe'],
                ['id' => 3, 'first_name' => 'Jane', 'last_name' => 'Duplicate'],
            ])
                ->unique('id', should_be_strict: true)
                ->values()
                ->toArray(),
            expected: [
                ['id' => 1, 'first_name' => 'John', 'last_name' => 'Doe'],
                ['id' => '1', 'first_name' => 'John', 'last_name' => 'Doe'],
                ['id' => 2, 'first_name' => 'John', 'last_name' => 'Doe'],
                ['id' => 3, 'first_name' => 'Jane', 'last_name' => 'Doe'],
            ],
        );
    }

    public function test_unique_key_dot_notation(): void
    {
        $collection = arr([
            [
                'id' => 1,
                'title' => 'First Post',
                'author' => ['id' => 1, 'name' => 'John Doe'],
            ],
            [
                'id' => 2,
                'title' => 'Second Post',
                'author' => ['id' => 2, 'name' => 'Jane Smith'],
            ],
            [
                'id' => 3,
                'title' => 'Third Post',
                'author' => ['id' => 1, 'name' => 'John Doe'],  // Duplicate author
            ],
            [
                'id' => 4,
                'title' => 'Fourth Post',
                'author' => ['id' => 3, 'name' => 'Alice Johnson'],
            ],
            [
                'id' => 5,
                'title' => 'Fifth Post',
                'author' => ['id' => 2, 'name' => 'Jane Smith'],  // Duplicate author
            ],
            [
                'id' => 6,
                'title' => 'Sixth Post',
                'author' => ['id' => 4, 'name' => 'Bob Brown'],
            ],
        ]);

        $this->assertSame(
            actual: $collection
                ->unique('author.id')
                ->values()
                ->toArray(),
            expected: [
                [
                    'id' => 1,
                    'title' => 'First Post',
                    'author' => ['id' => 1, 'name' => 'John Doe'],
                ],
                [
                    'id' => 2,
                    'title' => 'Second Post',
                    'author' => ['id' => 2, 'name' => 'Jane Smith'],
                ],
                [
                    'id' => 4,
                    'title' => 'Fourth Post',
                    'author' => ['id' => 3, 'name' => 'Alice Johnson'],
                ],
                [
                    'id' => 6,
                    'title' => 'Sixth Post',
                    'author' => ['id' => 4, 'name' => 'Bob Brown'],
                ],
            ],
        );
    }

    public function test_flip(): void
    {
        $this->assertSame(
            actual: arr([
                'first_name' => 'John',
                'last_name' => 'Doe',
            ])
                ->flip()
                ->toArray(),
            expected: [
                'John' => 'first_name',
                'Doe' => 'last_name',
            ],
        );
    }

    public function test_pad(): void
    {
        $this->assertSame(
            actual: arr([1, 2, 3])
                ->pad(4, 0)
                ->toArray(),
            expected: [1, 2, 3, 0],
        );

        $this->assertSame(
            actual: arr([1, 2, 3, 4, 5])
                ->pad(4, 0)
                ->toArray(),
            expected: [1, 2, 3, 4, 5],
        );

        $this->assertSame(
            actual: arr([1, 2, 3])
                ->pad(-4, 0)
                ->toArray(),
            expected: [0, 1, 2, 3],
        );

        $this->assertSame(
            actual: arr([1, 2, 3, 4, 5])
                ->pad(-4, 0)
                ->toArray(),
            expected: [1, 2, 3, 4, 5],
        );
    }

    public function test_add(): void
    {
        $collection = arr();
        $this->assertSame(
            actual: $collection
                ->add(1)
                ->toArray(),
            expected: [1],
        );

        $this->assertSame(
            actual: $collection
                ->add(2)
                ->toArray(),
            expected: [1, 2],
        );

        $this->assertSame(
            actual: $collection
                ->add('')
                ->toArray(),
            expected: [1, 2, ''],
        );

        $this->assertSame(
            actual: $collection
                ->add(null)
                ->toArray(),
            expected: [1, 2, '', null],
        );

        $this->assertSame(
            actual: $collection
                ->add(false)
                ->toArray(),
            expected: [1, 2, '', null, false],
        );

        $this->assertSame(
            actual: $collection
                ->add([])
                ->toArray(),
            expected: [1, 2, '', null, false, []],
        );

        $this->assertSame(
            actual: $collection
                ->add('name')
                ->toArray(),
            expected: [1, 2, '', null, false, [], 'name'],
        );
    }

    public function test_push_is_alias_of_add(): void
    {
        $first_collection = arr()
            ->add(42)
            ->add('Hello')
            ->add([])
            ->add(false)
            ->add(null);
        $second_collection = arr()
            ->push(42)
            ->push('Hello')
            ->push([])
            ->push(false)
            ->push(null);

        $this->assertTrue($first_collection->equals($second_collection));
    }

    public function test_pluck_without_arrays(): void
    {
        $this->assertSame(
            actual: arr([
                'name' => 'John',
                'age' => 42,
            ])
                ->pluck('name')
                ->toArray(),
            expected: [],
        );
    }

    public function test_pluck_basics(): void
    {
        $collection = arr([
            ['name' => 'John', 'age' => 42],
            ['name' => 'Jane', 'age' => 35],
            ['name' => 'Alice', 'age' => 28],
        ]);

        $this->assertSame(
            actual: $collection
                ->pluck('name')
                ->toArray(),
            expected: ['John', 'Jane', 'Alice'],
        );

        $this->assertSame(
            actual: $collection
                ->pluck('age')
                ->toArray(),
            expected: [42, 35, 28],
        );

        $this->assertSame(
            actual: $collection
                ->pluck('name', 'age')
                ->toArray(),
            expected: [
                42 => 'John',
                35 => 'Jane',
                28 => 'Alice',
            ],
        );

        $this->assertSame(
            actual: $collection
                ->pluck('age', 'name')
                ->toArray(),
            expected: [
                'John' => 42,
                'Jane' => 35,
                'Alice' => 28,
            ],
        );
    }

    public function test_pluck_dot_notation(): void
    {
        $collection = arr([
            [
                'id' => 1,
                'title' => 'First Post',
                'author' => ['id' => 1, 'name' => 'John Doe'],
            ],
            [
                'id' => 2,
                'title' => 'Second Post',
                'author' => ['id' => 2, 'name' => 'Jane Smith'],
            ],
            [
                'id' => 3,
                'title' => 'Third Post',
                'author' => ['id' => 1, 'name' => 'John Doe'],
            ],
            [
                'id' => 4,
                'title' => 'Fourth Post',
                'author' => ['id' => 3, 'name' => 'Alice Johnson'],
            ],
            [
                'id' => 5,
                'title' => 'Fifth Post',
                'author' => ['id' => 2, 'name' => 'Jane Smith'],
            ],
            [
                'id' => 6,
                'title' => 'Sixth Post',
                'author' => ['id' => 4, 'name' => 'Bob Brown'],
            ],
        ]);

        $this->assertSame(
            actual: $collection
                ->pluck('author.name')
                ->toArray(),
            expected: [
                'John Doe',
                'Jane Smith',
                'John Doe',
                'Alice Johnson',
                'Jane Smith',
                'Bob Brown',
            ],
        );

        $this->assertSame(
            actual: $collection
                ->pluck('author.name', 'id')
                ->toArray(),
            expected: [
                1 => 'John Doe',
                2 => 'Jane Smith',
                3 => 'John Doe',
                4 => 'Alice Johnson',
                5 => 'Jane Smith',
                6 => 'Bob Brown',
            ],
        );

        $this->assertSame(
            actual: $collection
                ->pluck('author.name', 'author.id')
                ->toArray(),
            expected: [
                1 => 'John Doe',
                2 => 'Jane Smith',
                3 => 'Alice Johnson',
                4 => 'Bob Brown',
            ],
        );
    }

    public function test_random(): void
    {
        $collection = arr([1, 2, 3, 4, 5, 6, 7, 8, 9, 10]);

        $random = $collection->random();
        $this->assertIsInt($random);
        $this->assertContains($random, $collection->toArray());

        $randoms = $collection->random(3);
        foreach ($randoms as $value) {
            $this->assertIsInt($value);
            $this->assertContains($value, $collection->toArray());
        }

        $this->assertCount(3, $randoms);
    }

    public function test_random_with_preserve_keys(): void
    {
        $collection = arr([
            'id' => 1,
            'title' => 'First Post',
            'author' => ['id' => 1, 'name' => 'John Doe'],
        ]);

        $randoms = $collection->random(3, preserveKey: true);

        $this->assertCount(3, $randoms);
        $this->assertArrayHasKey('id', $randoms);
        $this->assertArrayHasKey('title', $randoms);
        $this->assertArrayHasKey('author', $randoms);

        $randoms = $collection->random(2, preserveKey: true);

        $this->assertCount(2, array_intersect_key($collection->toArray(), $randoms->toArray()));
    }

    public function test_random_on_empty_array(): void
    {
        $collection = arr();

        $this->expectException(InvalidArgumentException::class);

        $collection->random();
    }

    public function test_random_with_count_superior_than_array_count(): void
    {
        $collection = arr([1, 2, 3, 4, 5, 6, 7, 8, 9, 10]);

        $this->expectException(InvalidArgumentException::class);

        $collection->random(15);
    }

    public function test_random_throw_exception_when_giving_negative_integer(): void
    {
        $collection = arr([1, 2, 3, 4, 5, 6, 7, 8, 9, 10]);

        $this->expectException(InvalidArgumentException::class);

        $collection->random(-1);
    }
}
