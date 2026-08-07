<?php

declare(strict_types=1);

namespace Tempest\Support\Tests\Ip;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use Tempest\Support\Ip\InvalidIpAddress;
use Tempest\Support\Ip\IpAddress;

/**
 * @internal
 */
final class IpAddressTest extends TestCase
{
    #[Test]
    #[TestWith(['203.0.113.9'])]
    #[TestWith(['2001:db8::1'])]
    #[TestWith(['::ffff:127.0.0.1'])]
    public function creating_from_a_valid_address(string $ip): void
    {
        $this->assertSame($ip, IpAddress::from($ip)->toString());
        $this->assertSame($ip, (string) IpAddress::from($ip));
    }

    #[Test]
    #[TestWith(['not-an-address'])]
    #[TestWith([''])]
    #[TestWith(['10.0.0.0/8'])]
    #[TestWith(['203.0.113.9:8080'])]
    public function creating_from_an_invalid_address_throws(string $ip): void
    {
        $this->expectException(InvalidIpAddress::class);

        IpAddress::from($ip);
    }

    #[Test]
    public function try_from_returns_null_for_an_invalid_address(): void
    {
        $this->assertNull(IpAddress::tryFrom('not-an-address'));
        $this->assertNull(IpAddress::tryFrom(null));
    }

    #[Test]
    public function try_from_passes_an_address_through(): void
    {
        $ip = IpAddress::from('203.0.113.9');

        $this->assertSame($ip, IpAddress::tryFrom($ip));
    }

    #[Test]
    #[TestWith(['127.0.0.1', '::ffff:127.0.0.1'])]
    #[TestWith(['2001:db8::1', '2001:0db8:0000:0000:0000:0000:0000:0001'])]
    #[TestWith(['203.0.113.9', '203.0.113.9'])]
    public function equality_is_determined_by_the_address_rather_than_the_notation(string $ip, string $other): void
    {
        $this->assertTrue(IpAddress::from($ip)->equals($other));
        $this->assertTrue(IpAddress::from($ip)->equals(IpAddress::from($other)));
        $this->assertTrue(IpAddress::from($other)->equals($ip));
    }

    #[Test]
    #[TestWith(['203.0.113.9', '203.0.113.10'])]
    #[TestWith(['2001:db8::1', '2001:db8::2'])]
    #[TestWith(['127.0.0.1', '::1'])]
    public function different_addresses_are_not_equal(string $ip, string $other): void
    {
        $this->assertFalse(IpAddress::from($ip)->equals($other));
    }

    #[Test]
    public function non_addresses_are_never_equal(): void
    {
        $ip = IpAddress::from('203.0.113.9');

        $this->assertFalse($ip->equals('not-an-address'));
        $this->assertFalse($ip->equals(''));
        $this->assertFalse($ip->equals(null));
    }

    #[Test]
    public function address_families(): void
    {
        $this->assertTrue(IpAddress::from('203.0.113.9')->isIpv4);
        $this->assertFalse(IpAddress::from('203.0.113.9')->isIpv6);

        $this->assertTrue(IpAddress::from('2001:db8::1')->isIpv6);
        $this->assertFalse(IpAddress::from('2001:db8::1')->isIpv4);

        // IPv4-mapped addresses are narrowed to IPv4.
        $this->assertTrue(IpAddress::from('::ffff:127.0.0.1')->isIpv4);
    }

    #[Test]
    public function matching_a_range(): void
    {
        $this->assertTrue(IpAddress::from('10.0.1.24')->matches('10.0.0.0/8'));
        $this->assertFalse(IpAddress::from('10.0.1.24')->matches('192.168.0.0/16'));
    }

    #[Test]
    public function matching_any_range(): void
    {
        $ip = IpAddress::from('10.0.1.24');

        $this->assertTrue($ip->matchesAny(['203.0.113.9', '10.0.0.0/8']));
        $this->assertFalse($ip->matchesAny(['203.0.113.9', '192.168.0.0/16']));
        $this->assertFalse($ip->matchesAny([]));
    }

    #[Test]
    public function private_addresses(): void
    {
        $this->assertTrue(IpAddress::from('10.0.1.24')->isPrivate);
        $this->assertTrue(IpAddress::from('::1')->isPrivate);
        $this->assertFalse(IpAddress::from('203.0.113.9')->isPrivate);
    }
}
