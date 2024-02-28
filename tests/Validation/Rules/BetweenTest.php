<?php

declare(strict_types=1);

namespace Tests\Tempest\Validation\Rules;

use PHPUnit\Framework\TestCase;
use Tempest\Validation\Rules\Between;

class BetweenTest extends TestCase
{
    /** @test */
    public function test_between()
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
