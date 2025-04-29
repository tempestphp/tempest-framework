<?php

declare(strict_types=1);

namespace Tempest\Validation\Tests\Unit\Rules;

use PHPUnit\Framework\TestCase;
use Tempest\Validation\Rules\IPv4;

/**
 * @internal
 */
final class IPv4Test extends TestCase
{
    public function test_ipv4_address(): void
    {
        $rule = new IPv4();

        $this->assertSame('Value should be a valid IPv4 address', $rule->message());

        $this->assertTrue($rule->isValid('192.168.0.1'));
        $this->assertTrue($rule->isValid('10.0.0.1'));
        $this->assertTrue($rule->isValid('172.16.0.1'));

        $this->assertFalse($rule->isValid('2001:0db8:85a3:0000:0000:8a2e:0370:7334'));
        $this->assertFalse($rule->isValid('2001:db8:85a3::8a2e:370:7334'));
    }

    public function test_ip_address_without_private_range(): void
    {
        $rule = new IPv4(allowPrivateRange: false);

        $this->assertFalse($rule->isValid('192.168.1.1'));
        $this->assertTrue($rule->isValid('210.221.151.70'));
    }

    public function test_ip_address_without_reserved_range(): void
    {
        $rule = new IPv4(allowReservedRange: false);

        $this->assertFalse($rule->isValid('169.254.0.0'));
        $this->assertTrue($rule->isValid('172.16.1.1'));
    }

    public function test_messages(): void
    {
        $rule = new IPv4();
        $this->assertSame('Value should be a valid IPv4 address', $rule->message());

        $rule = new IPv4(allowPrivateRange: false);
        $this->assertSame('Value should be a valid IPv4 address that is not in a private range', $rule->message());

        $rule = new IPv4(allowReservedRange: false);
        $this->assertSame('Value should be a valid IPv4 address that is not in a reserved range', $rule->message());

        $rule = new IPv4(allowPrivateRange: false, allowReservedRange: false);
        $this->assertSame('Value should be a valid IPv4 address that is not in a private range and not in a reserved range', $rule->message());
    }
}
