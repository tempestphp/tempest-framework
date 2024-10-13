<?php

declare(strict_types=1);

namespace Tempest\Validation\Tests\Rules;

use PHPUnit\Framework\TestCase;
use Tempest\Validation\Rules\Callback;

/**
 * @internal
 */
final class CallbackTest extends TestCase
{
    public function test_custom(): void
    {
        $rule = new Callback(fn ($value): bool => (bool) $value, 'does not match');

        $this->assertTrue($rule->isValid(true));

        $this->assertFalse($rule->isValid(false));

        $this->assertSame('does not match', $rule->message());
    }
}
