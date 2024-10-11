<?php

declare(strict_types=1);

namespace Tempest\Validation\Tests\Rules;

use PHPUnit\Framework\TestCase;
use Tempest\Validation\Rules\Custom;
use Tempest\Validation\Tests\Rules\Fixtures\CustomTestClass;

/**
 * @internal
 */
final class CustomTest extends TestCase
{
    public function test_custom(): void
    {
        $rule = new Custom(fn ($value): bool => (bool) $value, 'does not match');

        $this->assertTrue($rule->isValid(true));

        $this->assertFalse($rule->isValid(false));

        $this->assertSame('does not match', $rule->message());
    }
}
