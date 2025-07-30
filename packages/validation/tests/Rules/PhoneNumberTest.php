<?php

declare(strict_types=1);

namespace Tempest\Validation\Tests\Rules;

use PHPUnit\Framework\TestCase;
use Tempest\Validation\Rules\PhoneNumber;

/**
 * @internal
 */
final class PhoneNumberTest extends TestCase
{
    public function test_phone_number(): void
    {
        $rule = new PhoneNumber();

        $this->assertFalse($rule->isValid('this is not a phone number'));
        $this->assertFalse($rule->isValid('john.doe@example.com'));
        $this->assertTrue($rule->isValid('+1 (805) 380-4329'));
        $this->assertTrue($rule->isValid('+32 0497 88 93 11'));

        $rule = new PhoneNumber('US');

        $this->assertTrue($rule->isValid('(805) 380-4329'));

        $rule = new PhoneNumber('BE');

        $this->assertTrue($rule->isValid('0497 88 93 11'));
    }
}
