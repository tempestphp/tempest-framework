<?php

declare(strict_types=1);

namespace Tests\Tempest\Validation\Rules;

use Tempest\Validation\Rules\DoesNotStartWith;
use Tests\Tempest\TestCase;

class DoesNotStartWithTest extends TestCase
{
    /**
     *
     * @dataProvider dataSets
     *
     */
    public function test_rule(string $needle, string $stringToTest, bool $expected): void
    {
        $rule = new DoesNotStartWith($needle);

        $this->assertEquals($expected, $rule->isValid($stringToTest));
    }

    public static function dataSets(): array
    {
        return [
            'should return false if it starts with the given string' => ['test', 'test this is a test', false],
            'should return true if it does not start with the given string' => ['test', 'this is a test', true],
            'should return true for an empty string' => ['test', '', true],
            'should return true for a single non-matching character' => ['test', 'a', true],
            'should return false for a matching character string' => ['a', 'apple', false],
            'should return true if it starts with a different string' => ['test', 'best this is a test', true],
            'should return true if the string is the reverse of the given string' => ['test', 'tset', true],
            'should return false if the string and given string are the same' => ['test', 'test', false],
        ];
    }
}
