<?php

declare(strict_types=1);

namespace Tests\Tempest\Validation\Rules;

use PHPUnit\Framework\TestCase;
use Tempest\Validation\Rules\DoesNotEndWith;

class DoesNotEndWithTest extends TestCase
{
    /** @test */
    public function test_does_not_end_with()
    {
        $rule = new DoesNotEndWith(needle: 'ab');

        $this->assertFalse($rule->isValid('ab'));
        $this->assertFalse($rule->isValid('cab'));
        $this->assertTrue($rule->isValid('b'));
        $this->assertTrue($rule->isValid('3434'));
    }
}
