<?php

declare(strict_types=1);

namespace Tests\Tempest\Unit\Validation\Rules;

use Tempest\Validation\Rules\Uppercase;
use Tests\Tempest\TestCase;

class UppercaseTest extends TestCase
{
    public function test_uppercase()
    {
        $rule = new Uppercase();

        $this->assertSame('Value should be an uppercase string', $rule->message());

        $this->assertTrue($rule->isValid('ABC'));
        $this->assertTrue($rule->isValid('ÀBÇ'));
        $this->assertFalse($rule->isValid('abc'));
        $this->assertFalse($rule->isValid('àbç'));
        $this->assertFalse($rule->isValid('AbC'));
    }
}
