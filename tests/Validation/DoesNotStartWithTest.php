<?php

declare(strict_types=1);

namespace Tests\Tempest\Validation;

use PHPUnit\Framework\TestCase;
use Tempest\Validation\Rules\DoesNotStartWith;

class DoesNotStartWithTest extends TestCase
{
    /** @test */
    public function test_does_not_start_with()
    {
        $rule = new DoesNotStartWith(needle: 'ab');

        $this->assertFalse($rule->isValid('ab'));
        $this->assertFalse($rule->isValid('abc'));
        $this->assertTrue($rule->isValid('a'));
        $this->assertTrue($rule->isValid('3434'));
    }
}
