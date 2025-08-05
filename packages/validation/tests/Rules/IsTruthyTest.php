<?php

declare(strict_types=1);

namespace Tempest\Validation\Tests\Rules;

use PHPUnit\Framework\TestCase;
use Tempest\Validation\Rules\IsTruthy;

/**
 * @internal
 */
final class IsTruthyTest extends TestCase
{
    public function test_should_be_true(): void
    {
        $rule = new IsTruthy();

        $this->assertFalse($rule->isValid(false));
        $this->assertFalse($rule->isValid('false'));
        $this->assertFalse($rule->isValid(0));
        $this->assertFalse($rule->isValid('0'));
        $this->assertTrue($rule->isValid(true));
        $this->assertTrue($rule->isValid('true'));
        $this->assertTrue($rule->isValid(1));
        $this->assertTrue($rule->isValid('1'));
        $this->assertTrue($rule->isValid('yes'));
        $this->assertTrue($rule->isValid('enabled'));
        $this->assertTrue($rule->isValid('on'));
    }
}
