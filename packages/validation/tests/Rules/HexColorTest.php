<?php

declare(strict_types=1);

namespace Tempest\Validation\Tests\Rules;

use PHPUnit\Framework\TestCase;
use Tempest\Validation\Rules\HexColor;

/**
 * @internal
 */
final class HexColorTest extends TestCase
{
    public function test_uuid(): void
    {
        $rule = new HexColor();

        $this->assertSame('Value should be a valid hexadecimal color.', $rule->message());

        $this->assertFalse($rule->isValid('string_123'));
        $this->assertFalse($rule->isValid([])); // Should return false, not a TypeError from preg_match
        $this->assertFalse($rule->isValid('ffffff'));
        $this->assertFalse($rule->isValid('#fffffff'));
        $this->assertFalse($rule->isValid('#fffaa'));

        $this->assertTrue($rule->isValid('#ffffff'));
        $this->assertTrue($rule->isValid('#FFFFFF'));
        $this->assertTrue($rule->isValid('#FFFFFFAA'));
        $this->assertTrue($rule->isValid('#fff'));
        $this->assertTrue($rule->isValid('#fffa'));
    }
}
