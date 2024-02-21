<?php

declare(strict_types=1);

namespace Tests\Tempest\Validation\Rules;

use PHPUnit\Framework\TestCase;
use Tempest\Validation\Rules\Uuid;

class UuidTest extends TestCase
{
    public function test_uuid()
    {
        $rule = new Uuid();

        $this->assertFalse($rule->isValid('string_123'));
        $this->assertTrue($rule->isValid('cb2f46b4-d0c6-11ee-a506-0242ac120002')); // UUID v1
        $this->assertTrue($rule->isValid('0ec29141-3d58-4187-b664-2d93b7da0d31')); // UUID v4
        $this->assertTrue($rule->isValid('018dcc19-7e65-7c4b-9b14-9a11df3e0fdb')); // UUID v7
    }
}
