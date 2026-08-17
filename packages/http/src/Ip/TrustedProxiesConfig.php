<?php

declare(strict_types=1);

namespace Tempest\Http\Ip;

use Tempest\Support\Ip;
use Tempest\Support\Ip\IpAddress;

use function Tempest\Support\Ip\matches_any;

/**
 * Configures which reverse proxies may report the client address. No proxy is trusted by default.
 */
final class TrustedProxiesConfig
{
    /**
     * Trusts whichever address the request came from.
     */
    public const string ANY = '*';

    /**
     * Trusts any address that is not routed on the public internet, such as a proxy on the application's own network.
     */
    public const array PRIVATE_RANGES = Ip\PRIVATE_RANGES;

    /**
     * Note that proxies are declared as strings rather than as {@see IpAddress}, since a CIDR range is not itself an address.
     *
     * @param string[] $proxies Addresses or CIDR ranges of the reverse proxies in front of the application, or one of {@see self::PRIVATE_RANGES} and {@see self::ANY} when they have no stable address.
     * @param string[] $headers Headers carrying the forwarded address, in order of preference. Each is read as a comma-separated chain of hops, so headers that describe them differently, such as the `Forwarded` header defined by RFC 7239, are not supported.
     */
    public function __construct(
        public array $proxies = [],
        public array $headers = ['x-forwarded-for'],
    ) {}

    public function trusts(IpAddress|string $ip): bool
    {
        return in_array(self::ANY, $this->proxies, strict: true) || matches_any($ip, $this->proxies);
    }
}
