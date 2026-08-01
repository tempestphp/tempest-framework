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
function matches(string $ip, string $range): bool
{
    $prefix = null;

    if (str_contains($range, '/')) {
        [$range, $length] = explode('/', $range, limit: 2);

        if (! is_numeric($length)) {
            return false;
        }

        $prefix = (int) $length;
    }

    $address = to_bytes($ip);
    $subnet = to_bytes($range);

    if ($address === null || $subnet === null) {
        return false;
    }

    // Different lengths indicate different families, which never match.
    if (strlen($address) !== strlen($subnet)) {
        return false;
    }

    $bits = strlen($subnet) * 8;
    $prefix ??= $bits;

    if ($prefix < 0 || $prefix > $bits) {
        return false;
    }

    $wholeBytes = intdiv($prefix, 8);

    if ($wholeBytes > 0 && substr($address, 0, $wholeBytes) !== substr($subnet, 0, $wholeBytes)) {
        return false;
    }

    $remainingBits = $prefix % 8;

    if ($remainingBits === 0) {
        return true;
    }

    $mask = chr((0xFF << (8 - $remainingBits)) & 0xFF);

    return ($address[$wholeBytes] & $mask) === ($subnet[$wholeBytes] & $mask);
}

/**
 * Determines whether the given IP address falls within any of the given addresses or CIDR ranges.
 *
 * @param string[] $ranges
 */
function matches_any(string $ip, array $ranges): bool
{
    return array_any($ranges, static fn (string $range) => matches($ip, $range));
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
function is_private(string $ip): bool
{
    return matches_any($ip, PRIVATE_RANGES);
}

/**
 * Converts an IP address to its packed representation, or `null` when it is not an address.
 *
 * @internal
 */
function to_bytes(string $ip): ?string
{
    if (filter_var($ip, FILTER_VALIDATE_IP) === false) {
        return null;
    }

    $bytes = inet_pton($ip);

    if ($bytes === false) {
        return null;
    }

    if (strlen($bytes) === 16 && str_starts_with($bytes, "\0\0\0\0\0\0\0\0\0\0\xFF\xFF")) {
        return substr($bytes, 12);
    }

    return $bytes;
}
