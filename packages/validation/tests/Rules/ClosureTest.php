<?php

declare(strict_types=1);

namespace Tempest\Validation\Tests\Rules;

use PHPUnit\Framework\TestCase;
use Tempest\Validation\Rules\Closure;

/**
 * @internal
 */
final class ClosureTest extends TestCase
{
    public function test_closure_validation_passes(): void
    {
        $rule = new Closure(static fn (mixed $value): bool => str_contains((string) $value, '@'));
        $this->assertTrue($rule->isValid('user@example.com'));
        $this->assertTrue($rule->isValid('test@domain.org'));
    }

    public function test_closure_validation_fails(): void
    {
        $rule = new Closure(static fn (mixed $value): bool => str_contains((string) $value, '@'));

        $this->assertFalse($rule->isValid('username'));
        $this->assertFalse($rule->isValid('example.com'));
    }

    public function test_non_string_value_fails(): void
    {
        $rule = new Closure(static fn (mixed $value): bool => str_contains((string) $value, '@'));

        $this->assertFalse($rule->isValid(12345));
        $this->assertFalse($rule->isValid(null));
        $this->assertFalse($rule->isValid(false));
    }

    public function test_static_closure_required(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new Closure(fn (mixed $value): bool => str_contains((string) $value, '@'));
    }
}
