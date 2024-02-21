<?php

declare(strict_types=1);

namespace Tests\Tempest\Validation\Rules;

use PHPUnit\Framework\TestCase;
use Tempest\Validation\Rules\Ulid;

class UlidTest extends TestCase
{
    public function test_ulid()
    {
        $rule = new Ulid();

        $this->assertSame('Value should be a valid ULID', $rule->message());

        $this->assertTrue($rule->isValid('01FV8CE8P3XVZTVK0S6F05Z5ZA'));
        $this->assertFalse($rule->isValid('01FV8CE8P3XVZTVK0S6F05Z5ZU'));       // contains invalid character
        $this->assertFalse($rule->isValid('01FV8CE8P3XVZTVK0S6F05'));           // too short
        $this->assertFalse($rule->isValid('01FV8CE8P3XVZTVK0S6F05Z5ZAAAAA'));   // too long

    }
}
