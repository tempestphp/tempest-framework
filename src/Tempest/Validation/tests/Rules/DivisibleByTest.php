<?php

declare(strict_types=1);

namespace Tempest\Validation\Tests\Rules;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tempest\Validation\Rules\DivisibleBy;

/**
 * @internal
 */
#[CoversClass(DivisibleBy::class)]
final class DivisibleByTest extends TestCase
{
    public function test_it_works(): void
    {
        $rule = new DivisibleBy(5);

        $this->assertTrue($rule->isValid(10));
        $this->assertTrue($rule->isValid(5));
        $this->assertFalse($rule->isValid(0));

        $this->assertFalse($rule->isValid(3));
        $this->assertFalse($rule->isValid(4));
        $this->assertFalse($rule->isValid(6));

        $this->assertSame('Value should be divisible by 5', $rule->message());
    }
}
