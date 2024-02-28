<?php

declare(strict_types=1);

namespace Tests\Tempest\Unit\Validation\Rules;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Tempest\Validation\Rules\Count;

class CountTest extends TestCase
{
    /**
     * @dataProvider dataSets
     */
    public function test_count(Count $rule, array $stringToTest, bool $expected): void
    {
        $this->assertEquals($expected, $rule->isValid($stringToTest));
    }

    /**
     * @dataProvider dataSetsMessage
     */
    public function test_returns_the_proper_message_based_on_min_and_max_arguments(
        Count $rule,
        string $expectedMessage
    ): void {
        $this->assertEquals($expectedMessage, $rule->message());
    }

    public function test_throws_an_exception_if_neither_min_or_max_is_supplied(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Count();
    }

    public static function dataSetsMessage(): array
    {
        return [
            'Should provide correct message when both min and max limits are defined (1, 5)' => [
                new Count(min: 1, max: 5),
                'Array should contain between 1 and 5 items',
            ],
            'Should provide correct message when only min limit is defined (1)' => [
                new Count(min: 1),
                'Array should contain no less than 1 items',
            ],
            'Should provide correct message when only max limit is defined (5)' => [
                new Count(max: 5),
                'Array should contain no more than 5 items',
            ],
        ];
    }

    public static function dataSets(): array
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
