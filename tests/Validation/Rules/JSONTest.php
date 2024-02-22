<?php

declare(strict_types=1);

namespace Tests\Tempest\Validation\Rules;

use Tempest\Validation\Rules\JSON;
use Tests\Tempest\TestCase;
use ValueError;

class JSONTest extends TestCase
{
    public function test_it_returns_true_for_valid_json_string(): void
    {
        $rule = new JSON();
        $this->assertTrue($rule->isValid('{"test": "test"}'));
    }

    public function test_it_returns_false_for_invalid_json_string(): void
    {
        $rule = new JSON();
        $this->assertFalse($rule->isValid('{"test": test}'));
    }

    public function test_it_allows_to_specify_depth(): void
    {
        // Not sure if there is a better way of asserting that a php function was called with a given argument
        $this->expectException(ValueError::class);
        $rule = new JSON(depth: 0); // we intentionally send something that is not valid
        $rule->isValid('{"test": "test"}');
    }

    public function test_it_allows_to_specify_flags(): void
    {
        // Not sure if there is a better way of asserting that a php function was called with a given argument
        $this->expectException(ValueError::class);
        $rule = new JSON(flags: 232312312); // we intentionally send something that is not valid
        $rule->isValid('{"test": "test"}');
    }

    public function test_it_returns_the_proper_message(): void
    {
        $rule = new JSON();
        $this->assertEquals('Value should be a valid JSON string', $rule->message());
    }
}
