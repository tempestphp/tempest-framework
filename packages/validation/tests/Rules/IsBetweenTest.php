<?php

declare(strict_types=1);

namespace Tempest\Validation\Tests\Rules;

use PHPUnit\Framework\TestCase;
use Tempest\Validation\Rules\IsBetween;

/**
 * @internal
 */
final class IsBetweenTest extends TestCase
{
    public function test_between(): void
    {
        $rule = new IsBetween(min: 0, max: 10);

        $this->assertTrue($rule->isValid(0));
        $this->assertTrue($rule->isValid(10));
        $this->assertTrue($rule->isValid(5));
        $this->assertFalse($rule->isValid(11));
        $this->assertFalse($rule->isValid(-1));
    }
}
