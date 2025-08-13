<?php

declare(strict_types=1);

namespace Tempest\Validation\Tests\Rules;

use PHPUnit\Framework\TestCase;
use Tempest\Validation\Rules\IsIPv6;

/**
 * @internal
 */
final class IsIPv6Test extends TestCase
{
    public function test_ipv6_address(): void
    {
        $rule = new IsIPv6();

        $this->assertTrue($rule->isValid('2001:0db8:85a3:0000:0000:8a2e:0370:7334'));
        $this->assertTrue($rule->isValid('2a03:b0c0:3:d0::11f5:3001'));

        $this->assertFalse($rule->isValid('192.168.0.1'));
        $this->assertFalse($rule->isValid('10.0.0.1'));
        $this->assertFalse($rule->isValid('172.16.0.1'));
    }

    public function test_ip_address_without_private_range(): void
    {
        $rule = new IsIPv6(allowPrivateRange: false);
        $this->assertFalse($rule->isValid('fd36:ecf4:e32b:5e21:aaaa:aaaa:aaaa:aaaa'));
        $this->assertTrue($rule->isValid('2a03:b0c0:3:d0::11f5:3001'));

        $rule = new IsIPv6(allowPrivateRange: true);
        $this->assertTrue($rule->isValid('fd36:ecf4:e32b:5e21:aaaa:aaaa:aaaa:aaaa'));
        $this->assertTrue($rule->isValid('2a03:b0c0:3:d0::11f5:3001'));
    }

    public function test_ip_address_without_reserved_range(): void
    {
        $rule = new IsIPv6(allowReservedRange: false);
        $this->assertFalse($rule->isValid('::1'));
        $this->assertTrue($rule->isValid('2a03:b0c0:3:d0::11f5:3001'));

        $rule = new IsIPv6(allowReservedRange: true);
        $this->assertTrue($rule->isValid('2001:db8:ffff:ffff:ffff:ffff:ffff:ffff'));
        $this->assertTrue($rule->isValid('2a03:b0c0:3:d0::11f5:3001'));
    }
}
