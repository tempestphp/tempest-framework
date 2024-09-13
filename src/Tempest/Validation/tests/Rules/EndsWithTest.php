<?php

declare(strict_types=1);

namespace Tempest\Validation\Tests\Rules;

use PHPUnit\Framework\TestCase;
use Tempest\Validation\Rules\EndsWith;

/**
 * @internal
 * @small
 */
class EndsWithTest extends TestCase
{
    public function test_ends_with(): void
    {
        $rule = new EndsWith(needle: 'ab');

        $this->assertSame('Value should end with ab', $rule->message());

        $this->assertTrue($rule->isValid('ab'));
        $this->assertTrue($rule->isValid('cab'));
        $this->assertFalse($rule->isValid('b'));
        $this->assertFalse($rule->isValid('3434'));
    }
}
