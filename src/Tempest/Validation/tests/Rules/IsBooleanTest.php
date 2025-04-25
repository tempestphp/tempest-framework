<?php

declare(strict_types=1);

namespace Tempest\Validation\Tests\Rules;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tempest\Validation\Rules\IsBoolean;

/**
 * @internal
 */
#[CoversClass(IsBoolean::class)]
final class IsBooleanTest extends TestCase
{
    public function test_boolean(): void
    {
        $rule = new IsBoolean();

        $this->assertTrue($rule->isValid(true));
        $this->assertTrue($rule->isValid('true'));
        $this->assertTrue($rule->isValid(1));
        $this->assertTrue($rule->isValid('1'));
        $this->assertTrue($rule->isValid(false));
        $this->assertTrue($rule->isValid('false'));
        $this->assertTrue($rule->isValid(0));
        $this->assertTrue($rule->isValid('0'));
        $this->assertFalse($rule->isValid(5));
        $this->assertFalse($rule->isValid(2.5));
        $this->assertFalse($rule->isValid('string'));
    }

    public function test_boolean_message(): void
    {
        $rule = new IsBoolean();

        $this->assertSame('Value should represent a boolean value', $rule->message());
    }
}
