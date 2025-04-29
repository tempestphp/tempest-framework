<?php

declare(strict_types=1);

namespace Tempest\Validation\Tests\Rules;

use PHPUnit\Framework\TestCase;
use Tempest\Validation\Rules\Alpha;

/**
 * @internal
 */
final class AlphaTest extends TestCase
{
    public function test_alpha(): void
    {
        $rule = new Alpha();

        $this->assertSame('Value should only contain alphabetic characters', $rule->message());
        $this->assertFalse($rule->isValid('string123'));
        $this->assertTrue($rule->isValid('string'));
        $this->assertTrue($rule->isValid('STRING'));
        $this->assertFalse($rule->isValid([])); // Should return false, not a TypeError from preg_match
    }
}
