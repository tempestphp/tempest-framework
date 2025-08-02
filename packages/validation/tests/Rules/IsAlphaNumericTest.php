<?php

declare(strict_types=1);

namespace Tempest\Validation\Tests\Rules;

use PHPUnit\Framework\TestCase;
use Tempest\Validation\Rules\IsAlphaNumeric;

/**
 * @internal
 */
final class IsAlphaNumericTest extends TestCase
{
    public function test_alphanumeric(): void
    {
        $rule = new IsAlphaNumeric();

        $this->assertFalse($rule->isValid('string_123'));
        $this->assertTrue($rule->isValid('string123'));
        $this->assertTrue($rule->isValid('STRING123'));
        $this->assertFalse($rule->isValid([])); // Should return false, not a TypeError from preg_match
    }
}
