<?php

declare(strict_types=1);

namespace Tempest\Validation\Tests\Unit\Rules;

use PHPUnit\Framework\TestCase;
use Tempest\Validation\Rules\IsInteger;

/**
 * @internal
 */
final class IsIntegerTest extends TestCase
{
    public function test_integer(): void
    {
        $rule = new IsInteger();

        $this->assertTrue($rule->isValid(1));
        $this->assertTrue($rule->isValid('1'));
        $this->assertFalse($rule->isValid('a'));
        $this->assertFalse($rule->isValid(''));
        $this->assertFalse($rule->isValid(null));
        $this->assertFalse($rule->isValid(false));
        $this->assertFalse($rule->isValid([]));
    }
}
