<?php

declare(strict_types=1);

namespace Tests\Tempest\Unit\Validation\Rules;

use PHPUnit\Framework\TestCase;
use Tempest\Validation\Rules\ShouldBeTrue;

/**
 * @internal
 * @small
 */
class ShouldBeTrueTest extends TestCase
{
    public function test_should_be_true()
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

    public function test_should_be_true_message()
    {
        $rule = new ShouldBeTrue();

        $this->assertSame('Value should represent a boolean true value.', $rule->message());
    }
}
