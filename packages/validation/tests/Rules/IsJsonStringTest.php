<?php

declare(strict_types=1);

namespace Tempest\Validation\Tests\Rules;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Validation\Rules\IsJsonString;
use ValueError;

/**
 * @internal
 */
final class IsJsonStringTest extends TestCase
{
    #[Test]
    public function it_returns_true_for_valid_json_string(): void
    {
        $rule = new IsJsonString();
        $this->assertTrue($rule->isValid('{"test": "test"}'));
    }

    #[Test]
    public function it_returns_false_for_invalid_json_string(): void
    {
        $rule = new IsJsonString();
        $this->assertFalse($rule->isValid('{"test": test}'));
    }

    #[Test]
    public function it_allows_to_specify_depth(): void
    {
        // Not sure if there is a better way of asserting that a php function was called with a given argument
        $this->expectException(ValueError::class);
        $rule = new IsJsonString(depth: 0); // we intentionally send something that is not valid
        $rule->isValid('{"test": "test"}');
    }

    #[Test]
    public function it_allows_to_specify_flags(): void
    {
        // Not sure if there is a better way of asserting that a php function was called with a given argument
        $this->expectException(ValueError::class);
        $rule = new IsJsonString(flags: 232_312_312); // we intentionally send something that is not valid
        $rule->isValid('{"test": "test"}');
    }
}
