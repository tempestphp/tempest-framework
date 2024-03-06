<?php

declare(strict_types=1);

namespace Tests\Tempest\Unit\Support;

use PHPUnit\Framework\TestCase;
use Tempest\Support\ArrayHelper;

/**
 * @internal
 * @small
 */
class ArrayHelperTest extends TestCase
{
    public function test_getting_array_value_with_single_key()
    {
        $array = ['firstName' => 'Jim'];

        $value = ArrayHelper::get($array, 'firstName');

        $this->assertSame('Jim', $value);
    }

    public function test_getting_array_value_with_dot_notation()
    {
        $array = [
            'person' => [
                'firstName' => 'Jim',
            ],
        ];

        $value = ArrayHelper::get($array, 'person.firstName');

        $this->assertSame('Jim', $value);
    }

    public function test_getting_non_existent_value_with_dot_notation()
    {
        $array = [];

        $value = ArrayHelper::get($array, 'person.firstName', 'This is the default');

        $this->assertSame('This is the default', $value);
    }

    public function test_has_key_with_single_key()
    {
        $array = ['firstName' => 'Jim'];

        $this->assertTrue(ArrayHelper::has($array, 'firstName'));
        $this->assertFalse(ArrayHelper::has($array, 'lastName'));
    }

    public function test_has_key_with_dot_notation()
    {
        $array = [
            'person' => ['firstName' => 'Jim'],
        ];

        $this->assertTrue(ArrayHelper::has($array, 'person.firstName'));
        $this->assertFalse(ArrayHelper::has($array, 'person.lastName'));
    }

    public function test_setting_array_value_with_single_key()
    {
        $array = [];

        ArrayHelper::set($array, 'test', 'testing');

        $this->assertSame(
            ['test' => 'testing'],
            $array
        );
    }

    public function test_setting_array_value_with_dot_notation()
    {
        $array = [];

        ArrayHelper::set($array, 'test.key', 'Bob');

        $this->assertEqualsCanonicalizing(
            [
                'test' => ['key' => 'Bob'],
            ],
            $array
        );
    }

    public function test_unwrap_single_key()
    {
        $this->assertSame(
            ['a' => 'a'],
            (new ArrayHelper())->unwrap(['a' => 'a']),
        );
    }

    public function test_unwrap_nested_key()
    {
        $this->assertSame(
            [
                'a' => [
                    'b' => 'ab',
                ],
            ],
            (new ArrayHelper())->unwrap(['a.b' => 'ab']),
        );
    }

    public function test_unwrap_several_items()
    {
        $this->assertSame(
            ['a' => 'a', 'b' => 'b'],
            (new ArrayHelper())->unwrap(['a' => 'a', 'b' => 'b']),
        );
    }

    public function test_unwrap_nested_key_multiple_items()
    {
        $this->assertSame(
            [
                'a' => [
                    'x',
                    'y',
                ],
            ],
            (new ArrayHelper())->unwrap(['a.0' => 'x', 'a.1' => 'y']),
        );
    }

    public function test_unwrap_real()
    {
        $this->assertSame(
            [
                'author' => [
                    'name' => 'Brent',
                    'books' => [
                        ['title' => 'a'],
                        ['title' => 'b'],
                    ],
                ],
            ],
            (new ArrayHelper())->unwrap([
                'author.name' => 'Brent',
                'author.books.0.title' => 'a',
                'author.books.1.title' => 'b',
            ]),
        );
    }

    public function test_to_array_with_nested_property()
    {
        $this->assertSame(
            [
                'a' => [
                    'b' => 'ab',
                ],
            ],
            (new ArrayHelper())->toArray(
                key: 'a.b',
                value: 'ab',
            ),
        );
    }
}
