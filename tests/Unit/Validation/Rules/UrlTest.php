<?php

declare(strict_types=1);

namespace Tests\Tempest\Unit\Validation\Rules;

use PHPUnit\Framework\TestCase;
use Tempest\Validation\Rules\Url;

class UrlTest extends TestCase
{
    public function test_url()
    {
        $rule = new Url();

        $this->assertFalse($rule->isValid('this is not a url'));
        $this->assertFalse($rule->isValid('https://https://example.com'));
        $this->assertTrue($rule->isValid('https://example.com'));
        $this->assertTrue($rule->isValid('http://example.com'));
    }

    public function test_url_with_restricted_protocols()
    {
        $rule = new Url(['https']);

        $this->assertFalse($rule->isValid('http://example.com'));
        $this->assertTrue($rule->isValid('https://example.com'));
    }

    public function test_url_with_integer_value()
    {
        $rule = new Url();

        $this->assertFalse($rule->isValid(1));
    }

    public function test_url_message()
    {
        $rule = new Url();

        $this->assertSame('Value should be a valid URL', $rule->message());
    }
}
