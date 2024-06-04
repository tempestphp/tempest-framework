<?php

declare(strict_types=1);

namespace Tests\Tempest\Unit\Validation\Rules;

use PHPUnit\Framework\TestCase;
use Tempest\Validation\Rules\DoesNotEndWith;

/**
 * @internal
 * @small
 */
class DoesNotEndWithTest extends TestCase
{
    /**
     *
     * @dataProvider provide_rule_cases
     */
    public function test_rule(string $needle, string $stringToTest, bool $expected): void
    {
        $rule = new DoesNotEndWith($needle);

        $this->assertSame('Value should not end with ' . $needle, $rule->message());

        $this->assertEquals($expected, $rule->isValid($stringToTest));
    }

    public static function provide_rule_cases(): iterable
    {
        return [
            'should return false if it ends with the given string' => ['test', 'this is a test', false],
            'should return true if it does not end with the given string' => ['test', 'test this is a', true],
            'should return true for an empty string' => ['test', '', true],
            'should return true for a single non-matching character' => ['test', 'a', true],
            'should return false for a matching character string' => ['a', 'banana', false],
            'should return true if it ends with a different string' => [
                'test',
                'this is a test best',
                true,
            ],
            'should return true if the string is the reverse of the given string' => ['test', 'tset', true],
            'should return false if the string and given string are the same' => ['test', 'test', false],
        ];
    }
}
