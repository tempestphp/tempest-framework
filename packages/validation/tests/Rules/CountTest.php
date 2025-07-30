<?php

declare(strict_types=1);

namespace Tempest\Validation\Tests\Rules;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Tempest\Validation\Rules\Count;

/**
 * @internal
 */
final class CountTest extends TestCase
{
    #[DataProvider('provide_count_cases')]
    public function test_count(Count $rule, array $stringToTest, bool $expected): void
    {
        $this->assertEquals($expected, $rule->isValid($stringToTest));
    }

    public function test_throws_an_exception_if_neither_min_or_max_is_supplied(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Count();
    }

    public static function provide_count_cases(): iterable
    {
        return [
            'Should invalidate when array length is less than the minimum limit (1)' => [
                new Count(min: 1, max: 5),
                [],
                false,
            ],
            'Should validate when array length is exactly the minimum limit (1)' => [
                new Count(min: 1, max: 5),
                [1],
                true,
            ],
            'Should validate when array length is between the minimum (1) and maximum (5) limits' => [
                new Count(min: 1, max: 5),
                [1, 2, 3],
                true,
            ],
            'Should validate when array length is exactly at the maximum limit (5)' => [
                new Count(min: 1, max: 5),
                [1, 2, 3, 4, 5],
                true,
            ],
            'Should invalidate when array length is greater than the maximum limit (5)' => [
                new Count(min: 1, max: 5),
                [1, 2, 3, 4, 5, 6],
                false,
            ],
        ];
    }
}
