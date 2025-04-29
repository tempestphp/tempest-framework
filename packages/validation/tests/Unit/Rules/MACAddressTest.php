<?php

declare(strict_types=1);

namespace Tempest\Validation\Tests\Unit\Rules;

use PHPUnit\Framework\TestCase;
use Tempest\Validation\Rules\MACAddress;

/**
 * @internal
 */
final class MACAddressTest extends TestCase
{
    public function test_ip_address(): void
    {
        $rule = new MACAddress();

        $this->assertSame('Value should be a valid MAC Address', $rule->message());
        $this->assertTrue($rule->isValid('00:1A:2B:3C:4D:5E'));
        $this->assertTrue($rule->isValid('01-23-45-67-89-AB'));
        $this->assertTrue($rule->isValid('A1:B2:C3:D4:E5:F6'));
        $this->assertTrue($rule->isValid('a1:b2:c3:d4:e5:f6'));
        $this->assertTrue($rule->isValid('FF:FF:FF:FF:FF:FF'));

        $this->assertFalse($rule->isValid('00:1A:2B:3C:4D'));
        $this->assertFalse($rule->isValid('01-23-45-67-89-AB-CD'));
        $this->assertFalse($rule->isValid('A1:B2:C3:D4:E5:G6'));
        $this->assertFalse($rule->isValid('a1:b2:c3:d4:e5:f6:7'));
        $this->assertFalse($rule->isValid('FF:FF:FF:FF:FF'));
    }
}
