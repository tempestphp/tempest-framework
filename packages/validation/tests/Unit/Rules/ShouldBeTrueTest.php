<?php

declare(strict_types=1);

namespace Tempest\Validation\Tests\Unit\Rules;

use PHPUnit\Framework\TestCase;
use Tempest\Validation\Rules\ShouldBeTrue;

/**
 * @internal
 */
final class ShouldBeTrueTest extends TestCase
{
    public function test_should_be_true(): void
    {
        $rule = new ShouldBeTrue();

        $this->assertFalse($rule->isValid(false));
        $this->assertFalse($rule->isValid('false'));
        $this->assertFalse($rule->isValid(0));
        $this->assertFalse($rule->isValid('0'));
        $this->assertTrue($rule->isValid(true));
        $this->assertTrue($rule->isValid('true'));
        $this->assertTrue($rule->isValid(1));
        $this->assertTrue($rule->isValid('1'));
    }

    public function test_should_be_true_message(): void
    {
        $rule = new ShouldBeTrue();

        $this->assertSame('Value should represent a boolean true value.', $rule->message());
    }
}
