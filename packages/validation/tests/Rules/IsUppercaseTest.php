<?php

declare(strict_types=1);

namespace Tempest\Validation\Tests\Rules;

use PHPUnit\Framework\TestCase;
use Tempest\Validation\Rules\IsUppercase;

/**
 * @internal
 */
final class IsUppercaseTest extends TestCase
{
    public function test_uppercase(): void
    {
        $rule = new IsUppercase();

        $this->assertTrue($rule->isValid('ABC'));
        $this->assertTrue($rule->isValid('ÀBÇ'));
        $this->assertFalse($rule->isValid('abc'));
        $this->assertFalse($rule->isValid('àbç'));
        $this->assertFalse($rule->isValid('AbC'));
    }
}
