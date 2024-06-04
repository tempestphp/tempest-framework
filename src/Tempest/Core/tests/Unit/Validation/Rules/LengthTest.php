<?php

declare(strict_types=1);

namespace Tests\Tempest\Unit\Validation\Rules;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Tempest\Validation\Rules\Length;

/**
 * @internal
 * @small
 */
class LengthTest extends TestCase
{
    /**
     * @dataProvider provide_length_cases
     */
    public function test_length(Length $rule, string $stringToTest, bool $expected): void
    {
        $this->assertEquals($expected, $rule->isValid($stringToTest));
    }

    /**
     * @dataProvider provide_returns_the_proper_message_based_on_min_and_max_arguments_cases
     */
    public function test_returns_the_proper_message_based_on_min_and_max_arguments(
        Length $rule,
        string $expectedMessage
    ): void {
        $this->assertEquals($expectedMessage, $rule->message());
    }

    public function test_throws_an_exception_if_neither_min_or_max_is_supplied(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Length();
    }

    public static function provide_returns_the_proper_message_based_on_min_and_max_arguments_cases(): iterable
    {
        return [
            'Should provide correct message for string length validation with both minimum and maximum limits of 10 to 20' => [
                new Length(min: 10, max: 20),
                'Value should be between 10 and 20',
            ],
            'Should provide correct message for string length validation being no less than 10' => [
                new Length(min: 10),
                'Value should be no less than 10',
            ],
            'Should provide correct message for string length validation being no more than 10' => [
                new Length(max: 10),
                'Value should be no more than 10',
            ],
        ];
    }

    public static function provide_length_cases(): iterable
    {
        return [
            'Should return true when string meets minimum length requirement of 10' => [
                new Length(min: 10),
                'aaaaaaaaaa',
                true,
            ],
            'Should return true when string exceeds minimum length requirement of 10' => [
                new Length(min: 10),
                'aaaaaaaaaaa',
                true,
            ],
            'Should return false when string does not meet minimum length requirement of 10' => [
                new Length(min: 10),
                'aaaaaaaaa',
                false,
            ],
            'Should return true when string meets maximum length requirement of 5' => [
                new Length(max: 5),
                'aaaaa',
                true,
            ],
            'Should return true when string is shorter than maximum length requirement of 5' => [
                new Length(max: 5),
                'aaaa',
                true,
            ],
            'Should return false when string exceeds maximum length requirement of 5' => [
                new Length(max: 5),
                'aaaaaa',
                false,
            ],
            'Should return true when string is within minimum and maximum length requirement of 2-5' => [
                new Length(
                    min: 2,
                    max: 5
                ),
                'aaaaa',
                true,
            ],
            'Should return true when string is within minimum and maximum length requirement of 2-5 but shorter' => [
                new Length(
                    min: 2,
                    max: 5
                ),
                'aaaa',
                true,
            ],
            'Should return true when string meets minimum length requirement of 2 within 2-5 limit' => [
                new Length(
                    min: 2,
                    max: 5
                ),
                'aa',
                true,
            ],
            'Should return false when string does not meet minimum length requirement of 2 within 2-5 limit' => [
                new Length(
                    min: 2,
                    max: 5
                ),
                'a',
                false,
            ],
            'Should return false when string exceeds maximum length requirement of 5 within 2-5 limit' => [
                new Length(
                    min: 2,
                    max: 5
                ),
                'aaaaaa',
                false,
            ],
        ];
    }
}
