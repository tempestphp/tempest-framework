<?php

declare(strict_types=1);

namespace Tempest\Validation\Tests\Rules;

use PHPUnit\Framework\TestCase;
use Tempest\Validation\Rules\IP;

/**
 * @internal
 */
final class IPTest extends TestCase
{
    public function test_ip_address(): void
    {
        $rule = new IP();

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

    public function test_messages(): void
    {
        $rule = new IP();
        $this->assertSame('Value should be a valid IP address', $rule->message());

        $rule = new IP(allowPrivateRange: false);
        $this->assertSame('Value should be a valid IP address that is not in a private range', $rule->message());

        $rule = new IP(allowReservedRange: false);
        $this->assertSame('Value should be a valid IP address that is not in a reserved range', $rule->message());

        $rule = new IP(allowPrivateRange: false, allowReservedRange: false);
        $this->assertSame('Value should be a valid IP address that is not in a private range and not in a reserved range', $rule->message());
    }
}
