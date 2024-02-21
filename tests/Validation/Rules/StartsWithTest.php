<?php

declare(strict_types=1);

namespace Tests\Tempest\Validation\Rules;

use PHPUnit\Framework\TestCase;
use Tempest\Validation\Rules\StartsWith;

class StartsWithTest extends TestCase
{
    /** @test */
    public function test_starts_with()
    {
        $rule = new StartsWith(needle: 'ab');

        $this->assertTrue($rule->isValid('ab'));
        $this->assertTrue($rule->isValid('abc'));
        $this->assertFalse($rule->isValid('a'));
        $this->assertFalse($rule->isValid('3434'));
    }
}
