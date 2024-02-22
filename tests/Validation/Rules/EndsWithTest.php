<?php

declare(strict_types=1);

namespace Tests\Tempest\Validation\Rules;

use PHPUnit\Framework\TestCase;
use Tempest\Validation\Rules\EndsWith;

class EndsWithTest extends TestCase
{
    /** @test */
    public function test_ends_with()
    {
        $rule = new EndsWith(needle: 'ab');

        $this->assertSame('Value should end with ab', $rule->message());

        $this->assertTrue($rule->isValid('ab'));
        $this->assertTrue($rule->isValid('cab'));
        $this->assertFalse($rule->isValid('b'));
        $this->assertFalse($rule->isValid('3434'));
    }
}
