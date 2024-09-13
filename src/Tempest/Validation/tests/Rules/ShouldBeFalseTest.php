<?php

declare(strict_types=1);

namespace Tempest\Validation\Tests\Rules;

use PHPUnit\Framework\TestCase;
use Tempest\Validation\Rules\ShouldBeFalse;

/**
 * @internal
 * @small
 */
final class ShouldBeFalseTest extends TestCase
{
    public function test_should_be_false(): void
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

    public function test_should_be_false_message(): void
    {
        $rule = new ShouldBeFalse();

        $this->assertSame('Value should represent a boolean false value.', $rule->message());
    }
}
