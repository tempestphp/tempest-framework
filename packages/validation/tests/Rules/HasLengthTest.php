<?php

declare(strict_types=1);

namespace Tempest\Validation\Tests\Rules;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Tempest\Validation\Rules\HasLength;

/**
 * @internal
 */
final class HasLengthTest extends TestCase
{
    #[DataProvider('provide_length_cases')]
    public function test_length(HasLength $rule, string $stringToTest, bool $expected): void
    {
        $this->assertEquals($expected, $rule->isValid($stringToTest));
    }

    public function test_throws_an_exception_if_neither_min_or_max_is_supplied(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new HasLength();
    }

    public static function provide_length_cases(): iterable
    {
        return [
            'Should return true when string meets minimum length requirement of 10' => [
                new HasLength(min: 10),
                'aaaaaaaaaa',
                true,
            ],
            'Should return true when string exceeds minimum length requirement of 10' => [
                new HasLength(min: 10),
                'aaaaaaaaaaa',
                true,
            ],
            'Should return false when string does not meet minimum length requirement of 10' => [
                new HasLength(min: 10),
                'aaaaaaaaa',
                false,
            ],
            'Should return true when string meets maximum length requirement of 5' => [
                new HasLength(max: 5),
                'aaaaa',
                true,
            ],
            'Should return true when string is shorter than maximum length requirement of 5' => [
                new HasLength(max: 5),
                'aaaa',
                true,
            ],
            'Should return false when string exceeds maximum length requirement of 5' => [
                new HasLength(max: 5),
                'aaaaaa',
                false,
            ],
            'Should return true when string is within minimum and maximum length requirement of 2-5' => [
                new HasLength(
                    min: 2,
                    max: 5,
                ),
                'aaaaa',
                true,
            ],
            'Should return true when string is within minimum and maximum length requirement of 2-5 but shorter' => [
                new HasLength(
                    min: 2,
                    max: 5,
                ),
                'aaaa',
                true,
            ],
            'Should return true when string meets minimum length requirement of 2 within 2-5 limit' => [
                new HasLength(
                    min: 2,
                    max: 5,
                ),
                'aa',
                true,
            ],
            'Should return false when string does not meet minimum length requirement of 2 within 2-5 limit' => [
                new HasLength(
                    min: 2,
                    max: 5,
                ),
                'a',
                false,
            ],
            'Should return false when string exceeds maximum length requirement of 5 within 2-5 limit' => [
                new HasLength(
                    min: 2,
                    max: 5,
                ),
                'aaaaaa',
                false,
            ],
        ];
    }
}
