<?php

declare(strict_types=1);

namespace Tempest\Validation\Tests\Rules;

use PHPUnit\Framework\TestCase;
use Tempest\Validation\Rules\IsFalsy;

/**
 * @internal
 */
final class IsFalsyTest extends TestCase
{
    public function test_should_be_false(): void
    {
        $rule = new IsFalsy();

        $this->assertFalse($rule->isValid(true));
        $this->assertFalse($rule->isValid('true'));
        $this->assertFalse($rule->isValid(1));
        $this->assertFalse($rule->isValid('1'));
        $this->assertTrue($rule->isValid(false));
        $this->assertTrue($rule->isValid('false'));
        $this->assertTrue($rule->isValid(0));
        $this->assertTrue($rule->isValid('0'));
        $this->assertTrue($rule->isValid('no'));
        $this->assertTrue($rule->isValid('disabled'));
        $this->assertTrue($rule->isValid('off'));
    }
}
