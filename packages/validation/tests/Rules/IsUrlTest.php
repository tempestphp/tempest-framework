<?php

declare(strict_types=1);

namespace Tempest\Validation\Tests\Rules;

use PHPUnit\Framework\TestCase;
use Tempest\Validation\Rules\IsUrl;

/**
 * @internal
 */
final class IsUrlTest extends TestCase
{
    public function test_url(): void
    {
        $rule = new IsUrl();

        $this->assertFalse($rule->isValid('this is not a url'));
        $this->assertFalse($rule->isValid('https://https://example.com'));
        $this->assertTrue($rule->isValid('https://example.com'));
        $this->assertTrue($rule->isValid('http://example.com'));
    }

    public function test_url_with_restricted_protocols(): void
    {
        $rule = new IsUrl(['https']);

        $this->assertFalse($rule->isValid('http://example.com'));
        $this->assertTrue($rule->isValid('https://example.com'));
    }

    public function test_url_with_integer_value(): void
    {
        $rule = new IsUrl();

        $this->assertFalse($rule->isValid(1));
    }
}
