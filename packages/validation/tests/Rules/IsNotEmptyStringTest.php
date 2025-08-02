<?php

declare(strict_types=1);

namespace Tempest\Validation\Tests\Rules;

use PHPUnit\Framework\TestCase;
use Tempest\Validation\Rules\IsNotEmptyString;

/**
 * @internal
 */
final class IsNotEmptyStringTest extends TestCase
{
    public function test_not_empty(): void
    {
        $rule = new IsNotEmptyString();

        $this->assertTrue($rule->isValid('t'));
        $this->assertFalse($rule->isValid(''));
        $this->assertFalse($rule->isValid(1));
    }
}
