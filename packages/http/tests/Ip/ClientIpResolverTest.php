<?php

declare(strict_types=1);

namespace Tempest\Http\Tests\Ip;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use Tempest\Http\Ip\ClientIpResolver;
use Tempest\Http\Ip\TrustedProxiesConfig;
use Tempest\Http\RequestHeaders;
use Tempest\Support\Ip\IpAddress;

/**
 * @internal
 */
final class ClientIpResolverTest extends TestCase
{
    #[Test]
    public function connecting_address_is_used_when_no_proxy_is_trusted(): void
    {
        $ip = $this->resolve(
            remoteAddress: '203.0.113.9',
            headers: ['x-forwarded-for' => '198.51.100.7'],
        );

        $this->assertSame('203.0.113.9', $ip);
    }

    #[Test]
    public function forwarded_address_is_used_when_the_proxy_is_trusted(): void
    {
        $ip = $this->resolve(
            remoteAddress: '10.0.0.1',
            headers: ['x-forwarded-for' => '198.51.100.7'],
            proxies: ['10.0.0.0/8'],
        );

        $this->assertSame('198.51.100.7', $ip);
    }

    #[Test]
    public function the_nearest_untrusted_hop_is_the_client(): void
    {
        // Two hops forged by the client, then a CDN, then the load balancer.
        $ip = $this->resolve(
            remoteAddress: '10.0.0.1',
            headers: ['x-forwarded-for' => '1.1.1.1, 2.2.2.2, 198.51.100.7, 10.0.0.2'],
            proxies: ['10.0.0.0/8'],
        );

        $this->assertSame('198.51.100.7', $ip);
    }

    #[Test]
    public function chain_of_only_trusted_proxies_falls_back_to_the_outermost_hop(): void
    {
        $ip = $this->resolve(
            remoteAddress: '10.0.0.1',
            headers: ['x-forwarded-for' => '10.0.0.5, 10.0.0.2'],
            proxies: ['10.0.0.0/8'],
        );

        $this->assertSame('10.0.0.5', $ip);
    }

    #[Test]
    public function any_trusts_whichever_address_connects(): void
    {
        $ip = $this->resolve(
            remoteAddress: '172.18.0.1',
            headers: ['x-forwarded-for' => '198.51.100.7'],
            proxies: [TrustedProxiesConfig::ANY],
        );

        $this->assertSame('198.51.100.7', $ip);
    }

    #[Test]
    public function private_ranges_trust_a_proxy_on_the_same_network(): void
    {
        $ip = $this->resolve(
            remoteAddress: '172.18.0.1',
            headers: ['x-forwarded-for' => '198.51.100.7'],
            proxies: TrustedProxiesConfig::PRIVATE_RANGES,
        );

        $this->assertSame('198.51.100.7', $ip);
    }

    #[Test]
    public function private_ranges_do_not_trust_a_public_address(): void
    {
        $ip = $this->resolve(
            remoteAddress: '203.0.113.9',
            headers: ['x-forwarded-for' => '198.51.100.7'],
            proxies: TrustedProxiesConfig::PRIVATE_RANGES,
        );

        $this->assertSame('203.0.113.9', $ip);
    }

    #[Test]
    public function headers_are_consulted_in_order_of_preference(): void
    {
        $ip = $this->resolve(
            remoteAddress: '10.0.0.1',
            headers: [
                'x-forwarded-for' => '198.51.100.7',
                'cf-connecting-ip' => '198.51.100.8',
            ],
            proxies: ['10.0.0.0/8'],
            headerNames: ['cf-connecting-ip', 'x-forwarded-for'],
        );

        $this->assertSame('198.51.100.8', $ip);
    }

    #[Test]
    public function the_next_header_is_consulted_when_one_is_absent(): void
    {
        $ip = $this->resolve(
            remoteAddress: '10.0.0.1',
            headers: ['x-forwarded-for' => '198.51.100.7'],
            proxies: ['10.0.0.0/8'],
            headerNames: ['cf-connecting-ip', 'x-forwarded-for'],
        );

        $this->assertSame('198.51.100.7', $ip);
    }

    #[Test]
    public function ports_are_stripped_from_hops(): void
    {
        $this->assertSame('198.51.100.7', $this->resolve(
            remoteAddress: '10.0.0.1',
            headers: ['x-forwarded-for' => '198.51.100.7:41234'],
            proxies: ['10.0.0.0/8'],
        ));

        $this->assertSame('2001:db8::1', $this->resolve(
            remoteAddress: '10.0.0.1',
            headers: ['x-forwarded-for' => '[2001:db8::1]:41234'],
            proxies: ['10.0.0.0/8'],
        ));
    }

    #[Test]
    #[TestWith(['not-an-address'])]
    #[TestWith([''])]
    #[TestWith([', ,'])]
    public function forwarded_header_without_an_address_is_ignored(string $header): void
    {
        $ip = $this->resolve(
            remoteAddress: '10.0.0.1',
            headers: ['x-forwarded-for' => $header],
            proxies: ['10.0.0.0/8'],
        );

        $this->assertSame('10.0.0.1', $ip);
    }

    #[Test]
    public function invalid_hops_in_the_chain_are_discarded(): void
    {
        $ip = $this->resolve(
            remoteAddress: '10.0.0.1',
            headers: ['x-forwarded-for' => '198.51.100.7, not-an-address'],
            proxies: ['10.0.0.0/8'],
        );

        $this->assertSame('198.51.100.7', $ip);
    }

    #[Test]
    public function request_without_a_connecting_address_resolves_to_null(): void
    {
        $this->assertNull($this->resolve(remoteAddress: null, headers: []));
        $this->assertNull($this->resolve(remoteAddress: '', headers: []));
        $this->assertNull($this->resolve(remoteAddress: 'not-an-address', headers: []));
    }

    #[Test]
    public function resolved_address_is_a_value_object(): void
    {
        $resolver = new ClientIpResolver(new TrustedProxiesConfig());

        $ip = $resolver->resolve('::ffff:203.0.113.9', RequestHeaders::normalizeFromArray([]));

        $this->assertInstanceOf(IpAddress::class, $ip);
        $this->assertTrue($ip->equals('203.0.113.9'));
    }

    /**
     * @param array<string, string> $headers
     * @param string[] $proxies
     * @param string[] $headerNames
     */
    private function resolve(
        ?string $remoteAddress,
        array $headers,
        array $proxies = [],
        array $headerNames = ['x-forwarded-for'],
    ): ?string {
        $resolver = new ClientIpResolver(new TrustedProxiesConfig(
            proxies: $proxies,
            headers: $headerNames,
        ));

        return $resolver->resolve($remoteAddress, RequestHeaders::normalizeFromArray($headers))?->toString();
    }
}
