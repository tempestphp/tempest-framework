<?php

declare(strict_types=1);

namespace Tempest\Validation\Tests\Rules;

use PHPUnit\Framework\TestCase;
use Stringable;
use Tempest\Validation\Rules\IsString;

/**
 * @internal
 */
final class IsStringTest extends TestCase
{
    public function test_valid_non_nullable_string(): void
    {
        $rule = new IsString();

        $this->assertTrue($rule->isValid('test string'));
        $this->assertFalse($rule->isValid(false));
        $this->assertFalse($rule->isValid([]));
        $this->assertSame('Value should be a string', $rule->message());
    }

    public function test_valid_nullable_string(): void
    {
        $rule = new IsString(orNull: true);

        $this->assertTrue($rule->isValid('test string'));
        $this->assertTrue($rule->isValid(null));
        $this->assertFalse($rule->isValid(false));
        $this->assertFalse($rule->isValid([]));
    }

    public function test_valid_stringable_object(): void
    {
        $rule = new IsString();

        $stringable_object = new class implements Stringable {
            public function __toString(): string
            {
                return 'stringable object';
            }
        };

        $this->assertTrue($rule->isValid($stringable_object));
    }
}
