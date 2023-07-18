<?php

declare(strict_types=1);

namespace Tests\Tempest\Validation\Rules;

use PHPUnit\Framework\TestCase;
use Tempest\Validation\Rules\Length;

class LengthTest extends TestCase
{
    /** @test */
    public function test_length()
    {
        $rule = new Length(min: 10);

        $this->assertTrue($rule->isValid('aaaaaaaaaa'));
        $this->assertTrue($rule->isValid('aaaaaaaaaaa'));
        $this->assertFalse($rule->isValid('aaaaaaaaa'));

        $rule = new Length(max: 5);

        $this->assertTrue($rule->isValid('aaaaa'));
        $this->assertTrue($rule->isValid('aaaa'));
        $this->assertFalse($rule->isValid('aaaaaa'));

        $rule = new Length(min:2, max: 5);

        $this->assertTrue($rule->isValid('aaaaa'));
        $this->assertTrue($rule->isValid('aaaa'));
        $this->assertTrue($rule->isValid('aa'));
        $this->assertFalse($rule->isValid('a'));
        $this->assertFalse($rule->isValid('aaaaaa'));
    }
}
