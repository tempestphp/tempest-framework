<?php

declare(strict_types=1);

namespace Tests\Tempest\Validation\Rules;

use PHPUnit\Framework\TestCase;
use Tempest\Validation\Rules\Ip;

class IpTest extends TestCase
{
    public function test_ipv4()
    {
        $rule = new Ip();

        $this->assertFalse($rule->isValid('this is not a ipv4'));
        $this->assertFalse($rule->isValid('127.0'));
        $this->assertFalse($rule->isValid('127.0-0-1'));
        $this->assertTrue($rule->isValid('127.0.0.1'));
        $this->assertTrue($rule->isValid('192.186.0.1'));
    }

    public function test_ipv6()
    {
        $rule = new Ip();

        $this->assertFalse($rule->isValid('this is not a ipv6'));
        $this->assertFalse($rule->isValid('2001:db8:85a3'));
        $this->assertFalse($rule->isValid('2001-db8:85a3-8a2e:370:7334'));
        $this->assertTrue($rule->isValid('2001:db8:85a3::8a2e:370:7334'));
        $this->assertTrue($rule->isValid('2021:db8:85a3::8a2e:370:7334'));
    }

    public function test_ip_message()
    {
        $rule = new Ip();

        $this->assertSame('Value should be a valid IP.', $rule->message());
    }
}
