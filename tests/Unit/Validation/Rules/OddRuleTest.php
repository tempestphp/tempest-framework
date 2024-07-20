<?php

declare(strict_types=1);

namespace Tests\Tempest\Unit\Validation\Rules;

use PHPUnit\Framework\TestCase;
use Tempest\Validation\Rules\Odd;

/**
 * @internal
 * @small
 */
class OddRuleTest extends TestCase
{
    public function test_it_works(): void
    {
        $rule = new Odd();

        $this->assertFalse($rule->isValid(4));
        $this->assertFalse($rule->isValid(2));
        $this->assertFalse($rule->isValid(0));

        $this->assertTrue($rule->isValid(1));
        $this->assertTrue($rule->isValid(3));

        $this->assertSame('Value should be an odd number', $rule->message());
    }
}
