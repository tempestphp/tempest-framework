<?php

declare(strict_types=1);

namespace Tests\Tempest\Validation\Rules;

use Tempest\Validation\Rules\Enum;
use Tests\Tempest\TestCase;
use Tests\Tempest\Validation\Rules\Fixtures\SomeBackedEnum;
use Tests\Tempest\Validation\Rules\Fixtures\SomeEnum;
use UnexpectedValueException;

class EnumTest extends TestCase
{
    public function test_validating_enums()
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

    public function test_validating_backed_enums()
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

    public function test_enum_has_to_exist()
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
