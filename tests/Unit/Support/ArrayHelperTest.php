<?php

declare(strict_types=1);

namespace Tests\Tempest\Unit\Support;

use PHPUnit\Framework\TestCase;
use Tempest\Support\ArrayHelper;

class ArrayHelperTest extends TestCase
{
    /** @test */
    public function unwrap_single_key()
    {
        $this->assertSame(
            ['a' => 'a'],
            (new ArrayHelper())->unwrap(['a' => 'a']),
        );
    }

    /** @test */
    public function unwrap_nested_key()
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

    /** @test */
    public function unwrap_several_items()
    {
        $this->assertSame(
            ['a' => 'a', 'b' => 'b'],
            (new ArrayHelper())->unwrap(['a' => 'a', 'b' => 'b']),
        );
    }

    /** @test */
    public function unwrap_nested_key_multiple_items()
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

    /** @test */
    public function unwrap_real()
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

    /** @test */
    public function to_array_with_nested_property()
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
