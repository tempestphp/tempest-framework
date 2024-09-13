<?php

declare(strict_types=1);

namespace Tempest\Validation\Tests\Rules;

use PHPUnit\Framework\TestCase;
use Tempest\Validation\Rules\Url;

/**
 * @internal
 * @small
 */
class UrlTest extends TestCase
{
    public function test_url(): void
    {
        $rule = new Url();

        $this->assertFalse($rule->isValid('this is not a url'));
        $this->assertFalse($rule->isValid('https://https://example.com'));
        $this->assertTrue($rule->isValid('https://example.com'));
        $this->assertTrue($rule->isValid('http://example.com'));
    }

    public function test_url_with_restricted_protocols(): void
    {
        $rule = new Url(['https']);

        $this->assertFalse($rule->isValid('http://example.com'));
        $this->assertTrue($rule->isValid('https://example.com'));
    }

    public function test_url_with_integer_value(): void
    {
        $rule = new Url();

        $this->assertFalse($rule->isValid(1));
    }

    public function test_url_message(): void
    {
        $rule = new Url();

        $this->assertSame('Value should be a valid URL', $rule->message());
    }
}
