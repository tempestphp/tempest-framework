<?php

namespace Tests\Tempest\Validation\Rules;

use Tests\Tempest\TestCase;
use Tempest\Validation\Rules\Lowercase;

class LowercaseTest extends TestCase
{
    public function test_lowercase()
    {
        $rule = new Lowercase();
        
        $this->assertTrue($rule->isValid('abc'));
        $this->assertTrue($rule->isValid('àbç'));
        $this->assertFalse($rule->isValid('ABC'));
        $this->assertFalse($rule->isValid('ÀBÇ'));
        $this->assertFalse($rule->isValid('AbC'));
    }
}
