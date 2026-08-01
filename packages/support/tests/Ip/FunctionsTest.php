<?php

declare(strict_types=1);

namespace Tempest\Support\Tests\Ip;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use Tempest\Support\Ip;

/**
 * @internal
 */
final class FunctionsTest extends TestCase
{
    #[Test]
    #[TestWith(['203.0.113.9', '203.0.113.9'])]
    #[TestWith(['10.0.1.24', '10.0.0.0/8'])]
    #[TestWith(['10.0.1.24', '10.0.1.0/24'])]
    #[TestWith(['192.168.1.1', '0.0.0.0/0'])]
    #[TestWith(['2001:db8::1', '2001:db8::1'])]
    #[TestWith(['2001:db8::1', '2001:db8::/32'])]
    #[TestWith(['2001:db8::1', '::/0'])]
    #[TestWith(['::ffff:10.0.1.24', '10.0.0.0/8'])]
    public function matching_addresses(string $ip, string $range): void
    {
        $this->assertTrue(Ip\matches($ip, $range));
    }

    #[Test]
    #[TestWith(['203.0.113.9', '203.0.113.10'])]
    #[TestWith(['11.0.1.24', '10.0.0.0/8'])]
    #[TestWith(['10.0.2.24', '10.0.1.0/24'])]
    #[TestWith(['2001:db9::1', '2001:db8::/32'])]
    public function non_matching_addresses(string $ip, string $range): void
    {
        $this->assertFalse(Ip\matches($ip, $range));
    }

    #[Test]
    #[TestWith(['10.0.1.24', '::/0'])]
    #[TestWith(['2001:db8::1', '0.0.0.0/0'])]
    public function families_are_never_matched_against_each_other(string $ip, string $range): void
    {
        $this->assertFalse(Ip\matches($ip, $range));
    }

    #[Test]
    #[TestWith(['not-an-address', '10.0.0.0/8'])]
    #[TestWith(['10.0.1.24', 'not-a-range'])]
    #[TestWith(['10.0.1.24', '10.0.0.0/mask'])]
    #[TestWith(['10.0.1.24', '10.0.0.0/33'])]
    #[TestWith(['10.0.1.24', '10.0.0.0/-1'])]
    #[TestWith(['', ''])]
    public function malformed_input_never_matches(string $ip, string $range): void
    {
        $this->assertFalse(Ip\matches($ip, $range));
    }

    #[Test]
    public function matching_any_range(): void
    {
        $this->assertTrue(Ip\matches_any('10.0.1.24', ['203.0.113.9', '10.0.0.0/8']));
        $this->assertFalse(Ip\matches_any('10.0.1.24', ['203.0.113.9', '192.168.0.0/16']));
        $this->assertFalse(Ip\matches_any('10.0.1.24', []));
    }

    #[Test]
    #[TestWith(['127.0.0.1'])]
    #[TestWith(['10.0.1.24'])]
    #[TestWith(['172.16.0.1'])]
    #[TestWith(['192.168.1.1'])]
    #[TestWith(['169.254.0.1'])]
    #[TestWith(['::1'])]
    #[TestWith(['fd00::1'])]
    #[TestWith(['fe80::1'])]
    #[TestWith(['::ffff:10.0.1.24'])]
    public function private_addresses(string $ip): void
    {
        $this->assertTrue(Ip\is_private($ip));
    }

    #[Test]
    #[TestWith(['203.0.113.9'])]
    #[TestWith(['8.8.8.8'])]
    #[TestWith(['172.32.0.1'])]
    #[TestWith(['2001:db8::1'])]
    #[TestWith(['::ffff:203.0.113.9'])]
    #[TestWith(['not-an-address'])]
    public function public_addresses(string $ip): void
    {
        $this->assertFalse(Ip\is_private($ip));
    }
}
