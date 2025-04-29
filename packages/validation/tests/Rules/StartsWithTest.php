<?php

declare(strict_types=1);

namespace Tempest\Validation\Tests\Rules;

use PHPUnit\Framework\TestCase;
use Tempest\Validation\Rules\StartsWith;

/**
 * @internal
 */
final class StartsWithTest extends TestCase
{
    public function test_starts_with(): void
    {
        $rule = new StartsWith(needle: 'ab');

        $this->assertSame('Value should start with ab', $rule->message());

        $this->assertTrue($rule->isValid('ab'));
        $this->assertTrue($rule->isValid('abc'));
        $this->assertFalse($rule->isValid('a'));
        $this->assertFalse($rule->isValid('3434'));
    }
}
