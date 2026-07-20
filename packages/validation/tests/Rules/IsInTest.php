<?php

declare(strict_types=1);

namespace Tempest\Validation\Tests\Rules;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Validation\Rules\IsIn;

/**
 * @internal
 */
final class IsInTest extends TestCase
{
    #[Test]
    public function it_works(): void
    {
        $rule = new IsIn([4, 2, 0]);

        $this->assertTrue($rule->isValid(4));
        $this->assertTrue($rule->isValid(2));
        $this->assertTrue($rule->isValid(0));

        $this->assertFalse($rule->isValid(1));
        $this->assertFalse($rule->isValid(3));
    }
}
