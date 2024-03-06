<?php

namespace Tests\Tempest\Unit\Validation\Rules;

use PHPUnit\Framework\TestCase;
use Tempest\Validation\Rules\NotEmpty;

class NotEmptyTest extends TestCase
{
    public function test_not_empty()
    {
        $rule = new NotEmpty();

        $this->assertTrue($rule->isValid('t'));
        $this->assertFalse($rule->isValid(''));
        $this->assertFalse($rule->isValid(1));
    }
}
