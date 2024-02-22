<?php

declare(strict_types=1);

namespace Tests\Tempest\Validation\Rules;

use PHPUnit\Framework\TestCase;
use Tempest\Validation\Rules\IPAddress;

class IPAddressTest extends TestCase
{
    public function test_ip_address()
    {
        $rule = new IPAddress();

        $this->assertSame('Value should be a valid IP address', $rule->message());

        $this->assertTrue($rule->isValid('192.168.0.1'));
        $this->assertTrue($rule->isValid('10.0.0.1'));
        $this->assertTrue($rule->isValid('172.16.0.1'));
        $this->assertTrue($rule->isValid('2001:0db8:85a3:0000:0000:8a2e:0370:7334'));
        $this->assertTrue($rule->isValid('2001:db8:85a3::8a2e:370:7334'));

        $this->assertFalse($rule->isValid('256.0.0.1'));
        $this->assertFalse($rule->isValid('300.168.0.1'));
        $this->assertFalse($rule->isValid('192.168.0'));
        $this->assertFalse($rule->isValid('192.168.0.1.2'));
        $this->assertFalse($rule->isValid('192.168.0.1/24'));
    }

    public function test_ipv4_address()
    {
        $rule = new IPAddress(ipv4: true);

        $this->assertSame('Value should be a valid IPv4 address', $rule->message());

        $this->assertTrue($rule->isValid('192.168.0.1'));
        $this->assertTrue($rule->isValid('10.0.0.1'));
        $this->assertTrue($rule->isValid('172.16.0.1'));

        $this->assertFalse($rule->isValid('2001:0db8:85a3:0000:0000:8a2e:0370:7334'));
        $this->assertFalse($rule->isValid('2001:db8:85a3::8a2e:370:7334'));
    }

    public function test_ipv6_address()
    {
        $rule = new IPAddress(ipv6: true);

        $this->assertSame('Value should be a valid IPv6 address', $rule->message());

        $this->assertTrue($rule->isValid('2001:0db8:85a3:0000:0000:8a2e:0370:7334'));
        $this->assertTrue($rule->isValid('2001:db8:85a3::8a2e:370:7334'));

        $this->assertFalse($rule->isValid('192.168.0.1'));
        $this->assertFalse($rule->isValid('10.0.0.1'));
        $this->assertFalse($rule->isValid('172.16.0.1'));
    }

    public function test_ip_address_without_private_range()
    {
        $rule = new IPAddress(allowPrivateRange: false);

        $this->assertFalse($rule->isValid('192.168.1.1'));
        $this->assertTrue($rule->isValid('210.221.151.70'));
    }

    public function test_ip_address_without_reserved_range()
    {
        $rule = new IPAddress(allowReservedRange: false);

        $this->assertFalse($rule->isValid('169.254.0.0'));
        $this->assertTrue($rule->isValid('172.16.1.1'));
    }

    public function test_messages()
    {
        $rule = new IPAddress(ipv4: true);
        $this->assertSame('Value should be a valid IPv4 address', $rule->message());

        $rule = new IPAddress(ipv4: true, allowPrivateRange: false);
        $this->assertSame('Value should be a valid IPv4 address that is not in a private range', $rule->message());

        $rule = new IPAddress(ipv4: true, allowReservedRange: false);
        $this->assertSame('Value should be a valid IPv4 address that is not in a reserved range', $rule->message());

        $rule = new IPAddress(ipv4: true, allowPrivateRange: false, allowReservedRange: false);
        $this->assertSame('Value should be a valid IPv4 address that is not in a private range and not in a reserved range', $rule->message());

        $rule = new IPAddress(ipv6: true);
        $this->assertSame('Value should be a valid IPv6 address', $rule->message());

        $rule = new IPAddress(ipv6: true, allowPrivateRange: false);
        $this->assertSame('Value should be a valid IPv6 address that is not in a private range', $rule->message());

        $rule = new IPAddress(ipv6: true, allowReservedRange: false);
        $this->assertSame('Value should be a valid IPv6 address that is not in a reserved range', $rule->message());

        $rule = new IPAddress(ipv6: true, allowPrivateRange: false, allowReservedRange: false);
        $this->assertSame('Value should be a valid IPv6 address that is not in a private range and not in a reserved range', $rule->message());

        $rule = new IPAddress(ipv4: true, ipv6: true, allowPrivateRange: false, allowReservedRange: false);
        $this->assertSame('Value should be a valid IP address that is not in a private range and not in a reserved range', $rule->message());
    }
}
