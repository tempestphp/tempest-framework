<?php

declare(strict_types=1);

namespace Tempest\Validation\Tests\Rules;

use PHPUnit\Framework\TestCase;
use Tempest\Validation\Rules\AlphaNumeric;

/**
 * @internal
 */
final class AlphaNumericTest extends TestCase
{
    public function test_alphanumeric(): void
    {
        $rule = new AlphaNumeric();

        $this->assertSame('Value should only contain alphanumeric characters', $rule->message());
        $this->assertFalse($rule->isValid('string_123'));
        $this->assertTrue($rule->isValid('string123'));
        $this->assertTrue($rule->isValid('STRING123'));
    }
}
