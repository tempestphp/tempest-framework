<?php

declare(strict_types=1);

namespace Tests\Tempest\Validation\Rules;

use PHPUnit\Framework\TestCase;
use Tempest\Validation\Rules\Password;

class PasswordTest extends TestCase
{
    public function test_defaults()
    {
        $rule = new Password();

        $this->assertTrue($rule->isValid('123456789012'));
        $this->assertTrue($rule->isValid('aaaaaaaaaaaa'));
    }

    public function test_invalid_input()
    {
        $rule = new Password();
        $this->assertFalse($rule->isValid(123456789012));
        $this->assertFalse($rule->isValid([123456789012]));
    }

    public function test_minimum()
    {
        $rule = new Password(min: 4);
        $this->assertTrue($rule->isValid('12345'));
        $this->assertTrue($rule->isValid('1234'));
        $this->assertFalse($rule->isValid('123'));
    }

    public function test_mixed_case()
    {
        $rule = new Password(mixedCase: true);
        $this->assertTrue($rule->isValid('abcdEFGHIJKL'));
        $this->assertFalse($rule->isValid('abcdefghijkl'));
        $this->assertFalse($rule->isValid('ABCDEFGHIJKL'));
    }

    public function test_letters()
    {
        $rule = new Password(letters: true);
        $this->assertTrue($rule->isValid('12345678901a'));
        $this->assertFalse($rule->isValid('123456789012'));
    }

    public function test_numbers()
    {
        $rule = new Password(numbers: true);
        $this->assertTrue($rule->isValid('123456789012'));
        $this->assertTrue($rule->isValid('1aaaaaaaaaaa'));
        $this->assertFalse($rule->isValid('abcdefghijkl'));
    }

    public function test_symbols()
    {
        $rule = new Password(symbols: true);
        $this->assertTrue($rule->isValid('123456789012@'));
        $this->assertFalse($rule->isValid('123456789012'));
    }

    public function test_message()
    {
        $rule = new Password();
        $this->assertSame('Value should be a valid password', $rule->message());
    }
}
