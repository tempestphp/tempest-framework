<?php

declare(strict_types=1);

namespace Tempest\Validation\Tests\Rules;

use PHPUnit\Framework\TestCase;
use Tempest\Validation\Rules\IsNotIn;

/**
 * @internal
 */
final class IsNotInTest extends TestCase
{
    public function test_it_works(): void
    {
        $rule = new IsNotIn([4, 2, 0]);

        $this->assertFalse($rule->isValid(4));
        $this->assertFalse($rule->isValid(2));
        $this->assertFalse($rule->isValid(0));

        $this->assertTrue($rule->isValid(1));
        $this->assertTrue($rule->isValid(3));
    }
}
