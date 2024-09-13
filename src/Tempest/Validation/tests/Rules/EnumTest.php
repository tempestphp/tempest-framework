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
 * @small
 */
final class EnumTest extends TestCase
{
    public function test_validating_enums(): void
    {
        $rule = new Enum(SomeEnum::class);

        $this->assertSame(
            sprintf(
                'The value must be a valid enumeration [%s] case',
                SomeEnum::class
            ),
            $rule->message()
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
                SomeBackedEnum::class
            ),
            $rule->message()
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
                'Bob'
            )
        ));

        new Enum('Bob');
    }
}
