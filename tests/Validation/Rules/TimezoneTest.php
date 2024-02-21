<?php

declare(strict_types=1);

namespace Tests\Tempest\Validation\Rules;

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
}
