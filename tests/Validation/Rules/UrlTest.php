<?php

declare(strict_types=1);

namespace Tests\Tempest\Validation\Rules;

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
}
