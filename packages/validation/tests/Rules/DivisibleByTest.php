<?php

declare(strict_types=1);

namespace Tempest\Validation\Tests\Rules;

use PHPUnit\Framework\TestCase;
use Tempest\Validation\Rules\IsDivisibleBy;

/**
 * @internal
 */
final class DivisibleByTest extends TestCase
{
    public function test_it_works(): void
    {
        $rule = new IsDivisibleBy(5);

        $this->assertTrue($rule->isValid(10));
        $this->assertTrue($rule->isValid(5));
        $this->assertFalse($rule->isValid(0));

        $this->assertFalse($rule->isValid(3));
        $this->assertFalse($rule->isValid(4));
        $this->assertFalse($rule->isValid(6));
    }
}
