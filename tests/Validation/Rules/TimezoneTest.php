<?php

declare(strict_types=1);

namespace Tests\Tempest\Validation\Rules;

use DateTimeZone;
use PHPUnit\Framework\TestCase;
use Tempest\Validation\Rules\Timezone;

class TimezoneTest extends TestCase
{
    public function test_timezone()
    {
        $rule = new Timezone();

        $this->assertSame('Value should be a valid timezone', $rule->message());

        $this->assertFalse($rule->isValid('invalid_timezone'));
        $this->assertFalse($rule->isValid('Asia/Sydney'));
        $this->assertTrue($rule->isValid('America/New_York'));
        $this->assertTrue($rule->isValid('Europe/London'));
        $this->assertTrue($rule->isValid('Europe/Paris'));
        $this->assertTrue($rule->isValid('UTC'));
    }

    public function test_timezone_with_country_code()
    {
        $rule = new Timezone(DateTimeZone::PER_COUNTRY, 'AU');

        $this->assertFalse($rule->isValid('America/New_York'));
        $this->assertTrue($rule->isValid('Australia/Sydney'));
        $this->assertTrue($rule->isValid('Australia/Melbourne'));

        $rule = new Timezone(DateTimeZone::PER_COUNTRY, 'US');

        $this->assertFalse($rule->isValid('Europe/Paris'));
        $this->assertTrue($rule->isValid('America/New_York'));
        $this->assertTrue($rule->isValid('America/Los_Angeles'));
        $this->assertTrue($rule->isValid('America/Chicago'));
    }

    public function test_timezone_with_group()
    {
        $rule = new Timezone(DateTimeZone::ASIA);

        $this->assertFalse($rule->isValid('Africa/Nairobi'));
        $this->assertTrue($rule->isValid('Asia/Tokyo'));
        $this->assertTrue($rule->isValid('Asia/Hong_Kong'));
        $this->assertTrue($rule->isValid('Asia/Singapore'));

        $rule = new Timezone(DateTimeZone::INDIAN);

        $this->assertFalse($rule->isValid('Europe/Paris'));
        $this->assertTrue($rule->isValid('Indian/Reunion'));
        $this->assertTrue($rule->isValid('Indian/Comoro'));
    }
}
