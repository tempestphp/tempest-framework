<?php

declare(strict_types=1);

namespace Tempest\Validation\Tests\Rules;

use PHPUnit\Framework\TestCase;
use Tempest\Validation\Rules\Enum;
use Tempest\Validation\Tests\Rules\Fixtures\SomeBackedEnum;
use Tempest\Validation\Tests\Rules\Fixtures\SomeEnum;
use UnexpectedValueException;

/**
 * @internal
 */
final class EnumTest extends TestCase
{
    public function test_validating_enums(): void
    {
        $rule = new Enum(SomeEnum::class);

        $this->assertSame(
            sprintf(
                'The value must be a valid enumeration [%s] case',
                SomeEnum::class,
            ),
            $rule->message(),
        );

        $this->assertFalse($rule->isValid('NOPE_NOT_HERE'));
        $this->assertFalse($rule->isValid('NOPE_NOT_HERE_EITHER'));
        $this->assertTrue($rule->isValid('VALUE_1'));
        $this->assertTrue($rule->isValid('VALUE_2'));
    }

    public function test_validating_backed_enums(): void
    {
        $rule = new Enum(SomeBackedEnum::class);

        $this->assertSame(
            sprintf(
                'The value must be a valid enumeration [%s] case',
                SomeBackedEnum::class,
            ),
            $rule->message(),
        );

        $this->assertFalse($rule->isValid('three'));
        $this->assertFalse($rule->isValid('four'));
        $this->assertTrue($rule->isValid('one'));
        $this->assertTrue($rule->isValid('two'));
    }

    public function test_enum_has_to_exist(): void
    {
        $this->expectExceptionObject(new UnexpectedValueException(
            sprintf(
                'The enum parameter must be a valid enum. Was given [%s].',
                'Bob',
            ),
        ));

        new Enum('Bob');
    }

    public function test_validating_only_enums(): void
    {
        $rule = new Enum(SomeEnum::class);
        $this->assertTrue($rule->only(SomeEnum::VALUE_1)->isValid('VALUE_1'));
        $this->assertFalse($rule->only(SomeEnum::VALUE_2)->isValid('VALUE_1'));
    }

    public function test_validating_except_enums(): void
    {
        $rule = new Enum(SomeEnum::class);
        $this->assertTrue($rule->except(SomeEnum::VALUE_2)->isValid('VALUE_1'));
        $this->assertFalse($rule->except(SomeEnum::VALUE_1)->isValid('VALUE_1'));
    }

    public function test_validating_only_backed_enums(): void
    {
        $rule = new Enum(SomeBackedEnum::class);
        $this->assertTrue($rule->only(SomeBackedEnum::Test, SomeBackedEnum::Test2)->isValid('one'));
        $this->assertTrue($rule->only(SomeBackedEnum::Test)->only(SomeBackedEnum::Test2)->isValid('one'));
        $this->assertFalse($rule->only(SomeBackedEnum::Test2)->isValid('one'));
    }

    public function test_validating_except_backed_enums(): void
    {
        $rule = new Enum(SomeBackedEnum::class);
        $this->assertTrue($rule->except(SomeBackedEnum::Test2)->isValid('one'));
        $this->assertFalse($rule->except(SomeBackedEnum::Test)->isValid('one'));
    }
}
