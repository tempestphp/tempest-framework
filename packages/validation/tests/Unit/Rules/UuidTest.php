<?php

declare(strict_types=1);

namespace Tempest\Validation\Tests\Unit\Rules;

use PHPUnit\Framework\TestCase;
use Tempest\Validation\Rules\Uuid;

/**
 * @internal
 */
final class UuidTest extends TestCase
{
    public function test_uuid(): void
    {
        $rule = new Uuid();

        $this->assertSame('Value should contain a valid UUID', $rule->message());

        $this->assertFalse($rule->isValid('string_123'));
        $this->assertFalse($rule->isValid([])); // Should return false, not a TypeError from preg_match

        // UUID v1
        $this->assertTrue($rule->isValid('CB2F46B4-D0C6-11EE-A506-0242AC120002'));
        $this->assertTrue($rule->isValid('cb2f46b4-d0c6-11ee-a506-0242ac120002'));

        // UUID v4
        $this->assertTrue($rule->isValid('0EC29141-3D58-4187-B664-2D93B7DA0D31'));
        $this->assertTrue($rule->isValid('0ec29141-3d58-4187-b664-2d93b7da0d31'));

        // UUID v7
        $this->assertTrue($rule->isValid('018DCC19-7E65-7C4B-9B14-9A11DF3E0FDB'));
        $this->assertTrue($rule->isValid('018dcc19-7e65-7c4b-9b14-9a11df3e0fdb'));
    }
}
