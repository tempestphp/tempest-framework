<?php

declare(strict_types=1);

namespace Tempest\Http\Ip;

use Tempest\Http\RequestHeaders;
use Tempest\Support\Str;

/**
 * Resolves the address a request came from, reading forwarding headers only when it came from a proxy declared in {@see TrustedProxiesConfig}.
 */
final readonly class ClientIpResolver
{
    public function __construct(
        private TrustedProxiesConfig $trustedProxies,
    ) {}

    public function resolve(?string $remoteAddress, RequestHeaders $headers): ?string
    {
        $remoteAddress = $this->parse($remoteAddress);

        if ($remoteAddress === null || ! $this->trustedProxies->trusts($remoteAddress)) {
            return $remoteAddress;
        }

        foreach ($this->trustedProxies->headers as $header) {
            $chain = $this->parseChain($headers->get($header));

            if ($chain === []) {
                continue;
            }

            $client = array_find(
                array_reverse($chain),
                fn (string $candidate) => ! $this->trustedProxies->trusts($candidate),
            );

            // When every hop is trusted, the client is on the proxy network itself.
            return $client ?? $chain[0];
        }

        return $remoteAddress;
    }

    /**
     * @return string[]
     */
    private function parseChain(?string $header): array
    {
        if ($header === null) {
            return [];
        }

        return array_values(array_filter(array_map(
            $this->parse(...),
            explode(',', $header),
        )));
    }

    /**
     * Strips the port a hop may carry, discarding anything that is not a valid address.
     */
    private function parse(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim($value);

        if (str_starts_with($value, '[')) {
            // `[2001:db8::1]:8080`
            $value = Str\before_last(Str\after_first($value, '['), ']');
        } elseif (substr_count($value, ':') === 1) {
            // `203.0.113.9:8080`, whereas multiple colons indicate an IPv6 address.
            $value = Str\before_first($value, ':');
        }

        if (filter_var($value, FILTER_VALIDATE_IP) === false) {
            return null;
        }

        return $value;
    }
}
