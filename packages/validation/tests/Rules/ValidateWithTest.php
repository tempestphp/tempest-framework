<?php

declare(strict_types=1);

namespace Tempest\Validation\Tests\Rules;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;
use Tempest\Validation\Rules\ValidateWith;
use Tempest\Validation\Tests\Fixtures\ValidateWithObject;

/**
 * @internal
 */
final class ValidateWithTest extends TestCase
{
    #[Test]
    public function predicate_attribute_on_property_is_applied(): void
    {
        $reflection = new ReflectionProperty(ValidateWithObject::class, 'prop');
        $attributes = $reflection->getAttributes(ValidateWith::class);

        $this->assertCount(1, $attributes);

        $rule = $attributes[0]->newInstance();
        $this->assertTrue($rule->isValid('user@example'));
        $this->assertFalse($rule->isValid('invalid-prop'));
    }

    #[Test]
    public function closure_validation_passes(): void
    {
        $rule = new ValidateWith(static fn (mixed $value): bool => str_contains((string) $value, '@'));
        $this->assertTrue($rule->isValid('user@example.com'));
        $this->assertTrue($rule->isValid('test@domain.org'));
    }

    #[Test]
    public function closure_validation_fails(): void
    {
        $rule = new ValidateWith(static fn (mixed $value): bool => str_contains((string) $value, '@'));

        $this->assertFalse($rule->isValid('username'));
        $this->assertFalse($rule->isValid('example.com'));
    }

    #[Test]
    public function non_string_value_fails(): void
    {
        $rule = new ValidateWith(static fn (mixed $value): bool => str_contains((string) $value, '@'));

        $this->assertFalse($rule->isValid(12_345));
        $this->assertFalse($rule->isValid(null));
        $this->assertFalse($rule->isValid(false));
    }

    #[Test]
    public function static_closure_required(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new ValidateWith(fn (mixed $value): bool => str_contains((string) $value, '@'));
    }
}
