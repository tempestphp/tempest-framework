<?php

declare(strict_types=1);

namespace Tempest\Validation\Tests\Rules;

use PHPUnit\Framework\TestCase;
use Tempest\Validation\Rules\IsPassword;

/**
 * @internal
 */
final class IsPasswordTest extends TestCase
{
    public function test_defaults(): void
    {
        $rule = new IsPassword();

        $this->assertTrue($rule->isValid('123456789012'));
        $this->assertTrue($rule->isValid('aaaaaaaaaaaa'));
    }

    public function test_invalid_input(): void
    {
        $rule = new IsPassword();
        $this->assertFalse($rule->isValid(123456789012));
        $this->assertFalse($rule->isValid([123456789012]));
    }

    public function test_minimum(): void
    {
        $rule = new IsPassword(min: 4);
        $this->assertTrue($rule->isValid('12345'));
        $this->assertTrue($rule->isValid('1234'));
        $this->assertFalse($rule->isValid('123'));
    }

    public function test_mixed_case(): void
    {
        $rule = new IsPassword(mixedCase: true);
        $this->assertTrue($rule->isValid('abcdEFGHIJKL'));
        $this->assertFalse($rule->isValid('abcdefghijkl'));
        $this->assertFalse($rule->isValid('ABCDEFGHIJKL'));
    }

    public function test_letters(): void
    {
        $rule = new IsPassword(letters: true);
        $this->assertTrue($rule->isValid('12345678901a'));
        $this->assertFalse($rule->isValid('123456789012'));
    }

    public function test_numbers(): void
    {
        $rule = new IsPassword(numbers: true);
        $this->assertTrue($rule->isValid('123456789012'));
        $this->assertTrue($rule->isValid('1aaaaaaaaaaa'));
        $this->assertFalse($rule->isValid('abcdefghijkl'));
    }

    public function test_symbols(): void
    {
        $rule = new IsPassword(symbols: true);
        $this->assertTrue($rule->isValid('123456789012@'));
        $this->assertFalse($rule->isValid('123456789012'));
    }
}
