<?php

declare(strict_types=1);

namespace Tempest\Validation\Tests\Rules;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use stdClass;
use Tempest\Validation\Rules\IsEnum;
use Tempest\Validation\Tests\Rules\Fixtures\SomeBackedEnum;
use Tempest\Validation\Tests\Rules\Fixtures\SomeEnum;
use UnexpectedValueException;

/**
 * @internal
 */
final class IsEnumTest extends TestCase
{
    #[Test]
    public function validating_enums(): void
    {
        $rule = new IsEnum(SomeEnum::class);

        $this->assertFalse($rule->isValid('NOPE_NOT_HERE'));
        $this->assertFalse($rule->isValid('NOPE_NOT_HERE_EITHER'));
        $this->assertTrue($rule->isValid('VALUE_1'));
        $this->assertTrue($rule->isValid('VALUE_2'));
    }

    #[Test]
    public function validating_backed_enums(): void
    {
        $rule = new IsEnum(SomeBackedEnum::class);

        $this->assertFalse($rule->isValid('three'));
        $this->assertFalse($rule->isValid('four'));
        $this->assertTrue($rule->isValid('one'));
        $this->assertTrue($rule->isValid('two'));
    }

    #[Test]
    public function validating_values_with_non_matching_types(): void
    {
        $backed = new IsEnum(SomeBackedEnum::class);

        $this->assertFalse($backed->isValid(5));
        $this->assertFalse($backed->isValid(1.5));
        $this->assertFalse($backed->isValid(true));
        $this->assertFalse($backed->isValid([]));
        $this->assertFalse($backed->isValid(new stdClass()));

        $pure = new IsEnum(SomeEnum::class);

        $this->assertFalse($pure->isValid(5));
        $this->assertFalse($pure->isValid([]));
    }

    #[Test]
    public function enum_has_to_exist(): void
    {
        $this->expectExceptionObject(new UnexpectedValueException(sprintf('The enum parameter must be a valid enum. Was given [%s].', 'Bob')));

        new IsEnum('Bob');
    }

    #[Test]
    public function validating_only_enums(): void
    {
        $rule = new IsEnum(SomeEnum::class);
        $this->assertTrue($rule->only(SomeEnum::VALUE_1)->isValid('VALUE_1'));
        $this->assertFalse($rule->only(SomeEnum::VALUE_2)->isValid('VALUE_1'));
    }

    #[Test]
    public function validating_except_enums(): void
    {
        $rule = new IsEnum(SomeEnum::class);
        $this->assertTrue($rule->except(SomeEnum::VALUE_2)->isValid('VALUE_1'));
        $this->assertFalse($rule->except(SomeEnum::VALUE_1)->isValid('VALUE_1'));
    }

    #[Test]
    public function validating_only_backed_enums(): void
    {
        $rule = new IsEnum(SomeBackedEnum::class);
        $this->assertTrue($rule->only(SomeBackedEnum::Test, SomeBackedEnum::Test2)->isValid('one'));
        $this->assertTrue($rule->only(SomeBackedEnum::Test)->only(SomeBackedEnum::Test2)->isValid('one'));
        $this->assertFalse($rule->only(SomeBackedEnum::Test2)->isValid('one'));
    }

    #[Test]
    public function validating_except_backed_enums(): void
    {
        $rule = new IsEnum(SomeBackedEnum::class);
        $this->assertTrue($rule->except(SomeBackedEnum::Test2)->isValid('one'));
        $this->assertFalse($rule->except(SomeBackedEnum::Test)->isValid('one'));
    }

    #[Test]
    public function validating_with_or_null(): void
    {
        $rule = new IsEnum(enum: SomeEnum::class, orNull: true);

        $this->assertTrue(condition: $rule->isValid(value: null));
        $this->assertFalse(condition: $rule->isValid(value: 'NOPE_NOT_HERE'));
        $this->assertTrue(condition: $rule->isValid(value: 'VALUE_1'));
    }

    #[Test]
    public function failing_to_validate_with_or_null(): void
    {
        $rule = new IsEnum(enum: SomeEnum::class, orNull: false);

        $this->assertFalse(condition: $rule->isValid(value: null));
        $this->assertFalse(condition: $rule->isValid(value: 'NOPE_NOT_HERE'));
        $this->assertTrue(condition: $rule->isValid(value: 'VALUE_1'));
    }
}
