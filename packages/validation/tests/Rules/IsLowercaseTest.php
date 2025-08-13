<?php

declare(strict_types=1);

namespace Tempest\Validation\Tests\Rules;

use PHPUnit\Framework\TestCase;
use Tempest\Validation\Rules\IsLowercase;

/**
 * @internal
 */
final class IsLowercaseTest extends TestCase
{
    public function test_lowercase(): void
    {
        $rule = new IsLowercase();

        $this->assertTrue($rule->isValid('abc'));
        $this->assertTrue($rule->isValid('àbç'));
        $this->assertFalse($rule->isValid('ABC'));
        $this->assertFalse($rule->isValid('ÀBÇ'));
        $this->assertFalse($rule->isValid('AbC'));
    }
}
