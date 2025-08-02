<?php

declare(strict_types=1);

namespace Tempest\Validation\Tests\Rules;

use PHPUnit\Framework\TestCase;
use Tempest\Validation\Rules\IsAlpha;

/**
 * @internal
 */
final class IsAlphaTest extends TestCase
{
    public function test_alpha(): void
    {
        $rule = new IsAlpha();

        $this->assertFalse($rule->isValid('string123'));
        $this->assertTrue($rule->isValid('string'));
        $this->assertTrue($rule->isValid('STRING'));
        $this->assertFalse($rule->isValid([])); // Should return false, not a TypeError from preg_match
    }
}
