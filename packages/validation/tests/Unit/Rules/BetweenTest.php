<?php

declare(strict_types=1);

namespace Tempest\Validation\Tests\Unit\Rules;

use PHPUnit\Framework\TestCase;
use Tempest\Validation\Rules\Between;

/**
 * @internal
 */
final class BetweenTest extends TestCase
{
    public function test_between(): void
    {
        $rule = new Between(min: 0, max: 10);

        $this->assertSame('Value should be between 0 and 10', $rule->message());

        $this->assertTrue($rule->isValid(0));
        $this->assertTrue($rule->isValid(10));
        $this->assertTrue($rule->isValid(5));
        $this->assertFalse($rule->isValid(11));
        $this->assertFalse($rule->isValid(-1));
    }
}
