<?php

declare(strict_types=1);

namespace Tempest\Validation\Tests\Rules;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tempest\Validation\Rules\IsFloat;

/**
 * @internal
 */
#[CoversClass(IsFloat::class)]
final class IsFloatTest extends TestCase
{
    public function test_float(): void
    {
        $rule = new IsFloat();

        $this->assertTrue($rule->isValid(1));
        $this->assertTrue($rule->isValid(0.1));
        $this->assertTrue($rule->isValid('0.1'));
        $this->assertFalse($rule->isValid('a'));
        $this->assertFalse($rule->isValid(''));
        $this->assertFalse($rule->isValid(null));
        $this->assertFalse($rule->isValid(false));
        $this->assertFalse($rule->isValid([]));
    }
}
