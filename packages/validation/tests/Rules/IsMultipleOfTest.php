<?php

declare(strict_types=1);

namespace Tempest\Validation\Tests\Rules;

use PHPUnit\Framework\TestCase;
use Tempest\Validation\Rules\IsMultipleOf;

/**
 * @internal
 */
final class IsMultipleOfTest extends TestCase
{
    public function test_it_works(): void
    {
        $rule = new IsMultipleOf(5);

        $this->assertTrue($rule->isValid(10));
        $this->assertTrue($rule->isValid(5));
        $this->assertTrue($rule->isValid(0));

        $this->assertFalse($rule->isValid(3));
        $this->assertFalse($rule->isValid(4));
        $this->assertFalse($rule->isValid(6));
    }
}
