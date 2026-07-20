<?php

declare(strict_types=1);

namespace Tempest\Validation\Tests\Rules;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Validation\Rules\IsPassword;

/**
 * @internal
 */
final class IsPasswordTest extends TestCase
{
    #[Test]
    public function defaults(): void
    {
        $rule = new IsPassword();

        $this->assertTrue($rule->isValid('123456789012'));
        $this->assertTrue($rule->isValid('aaaaaaaaaaaa'));
    }

    #[Test]
    public function invalid_input(): void
    {
        $rule = new IsPassword();
        $this->assertFalse($rule->isValid(123_456_789_012));
        $this->assertFalse($rule->isValid([123_456_789_012]));
    }

    #[Test]
    public function minimum(): void
    {
        $rule = new IsPassword(min: 4);
        $this->assertTrue($rule->isValid('12345'));
        $this->assertTrue($rule->isValid('1234'));
        $this->assertFalse($rule->isValid('123'));
    }

    #[Test]
    public function mixed_case(): void
    {
        $rule = new IsPassword(mixedCase: true);
        $this->assertTrue($rule->isValid('abcdEFGHIJKL'));
        $this->assertFalse($rule->isValid('abcdefghijkl'));
        $this->assertFalse($rule->isValid('ABCDEFGHIJKL'));
    }

    #[Test]
    public function letters(): void
    {
        $rule = new IsPassword(letters: true);
        $this->assertTrue($rule->isValid('12345678901a'));
        $this->assertFalse($rule->isValid('123456789012'));
    }

    #[Test]
    public function numbers(): void
    {
        $rule = new IsPassword(numbers: true);
        $this->assertTrue($rule->isValid('123456789012'));
        $this->assertTrue($rule->isValid('1aaaaaaaaaaa'));
        $this->assertFalse($rule->isValid('abcdefghijkl'));
    }

    #[Test]
    public function symbols(): void
    {
        $rule = new IsPassword(symbols: true);
        $this->assertTrue($rule->isValid('123456789012@'));
        $this->assertFalse($rule->isValid('123456789012'));
    }
}
