<?php

declare(strict_types=1);

namespace Tests\Tempest\Unit\Validation\Rules;

use PHPUnit\Framework\TestCase;
use Tempest\Validation\Rules\In;

/**
 * @internal
 * @small
 */
class InTest extends TestCase
{
    public function test_it_works(): void
    {
        $rule = new In([4, 2, 0]);

        $this->assertTrue($rule->isValid(4));
        $this->assertTrue($rule->isValid(2));
        $this->assertTrue($rule->isValid(0));

        $this->assertFalse($rule->isValid(1));
        $this->assertFalse($rule->isValid(3));

        $this->assertSame('Value should be one of: 4, 2, 0', $rule->message());
    }
}
