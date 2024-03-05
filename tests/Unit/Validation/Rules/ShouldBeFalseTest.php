<?php

declare(strict_types=1);

namespace Tests\Tempest\Unit\Validation\Rules;

use PHPUnit\Framework\TestCase;
use Tempest\Validation\Rules\ShouldBeFalse;

/**
 * @internal
 * @small
 */
class ShouldBeFalseTest extends TestCase
{
    public function test_should_be_false()
    {
        $rule = new ShouldBeFalse();

        $this->assertFalse($rule->isValid(true));
        $this->assertFalse($rule->isValid('true'));
        $this->assertFalse($rule->isValid(1));
        $this->assertFalse($rule->isValid('1'));
        $this->assertTrue($rule->isValid(false));
        $this->assertTrue($rule->isValid('false'));
        $this->assertTrue($rule->isValid(0));
        $this->assertTrue($rule->isValid('0'));
    }

    public function test_should_be_false_message()
    {
        $rule = new ShouldBeFalse();

        $this->assertSame('Value should represent a boolean false value.', $rule->message());
    }
}
