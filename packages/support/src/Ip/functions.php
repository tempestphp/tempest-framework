<?php

declare(strict_types=1);

namespace Tempest\Support\Ip;

/**
 * Determines whether the given IP address falls within the given address or CIDR range.
 * Addresses are never matched across families, although IPv4-mapped IPv6 addresses such as `::ffff:127.0.0.1` are compared as IPv4.
 *
 * ### Example
 * ```php
 * matches('10.0.1.24', '10.0.0.0/8'); // true
 * matches('10.0.1.24', '10.0.1.24'); // true
 * matches('10.0.1.24', '::/0'); // false
 * ```
 */
function matches(IpAddress|string $ip, IpAddress|string $range): bool
{
    return IpAddress::tryFrom($ip)?->matches($range) ?? false;
}

/**
 * Determines whether the given IP address falls within any of the given addresses or CIDR ranges.
 *
 * @param iterable<IpAddress|string> $ranges
 */
function matches_any(IpAddress|string $ip, iterable $ranges): bool
{
    return IpAddress::tryFrom($ip)?->matchesAny($ranges) ?? false;
}

/**
 * Determines whether the given IP address belongs to a range that is not routed on the public internet, such as a loopback or private-use address.
 *
 * ### Example
 * ```php
 * is_private('10.0.1.24'); // true
 * is_private('203.0.113.9'); // false
 * ```
 */
function is_private(IpAddress|string $ip): bool
{
    return IpAddress::tryFrom($ip)?->isPrivate === true;
}

/**
 * Converts an IP address to its packed representation, or `null` when it is not an address.
 */
function to_bytes(IpAddress|string $ip): ?string
{
    return IpAddress::tryFrom($ip)?->bytes;
}
