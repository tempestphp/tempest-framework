<?php

declare(strict_types=1);

namespace Tempest\Validation\Tests\Rules;

use PHPUnit\Framework\TestCase;
use Tempest\Validation\Rules\IsBoolean;

/**
 * @internal
 */
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
        $this->assertTrue($rule->isValid('yes'));
        $this->assertTrue($rule->isValid('no'));
        $this->assertTrue($rule->isValid('enabled'));
        $this->assertTrue($rule->isValid('disabled'));
        $this->assertTrue($rule->isValid('off'));
        $this->assertTrue($rule->isValid('on'));
        $this->assertFalse($rule->isValid(5));
        $this->assertFalse($rule->isValid(2.5));
        $this->assertFalse($rule->isValid('string'));
    }
}
