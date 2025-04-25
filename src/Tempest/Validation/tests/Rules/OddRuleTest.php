<?php

declare(strict_types=1);

namespace Tempest\Validation\Tests\Rules;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tempest\Validation\Rules\Odd;

/**
 * @internal
 */
#[CoversClass(Odd::class)]
final class OddRuleTest extends TestCase
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
