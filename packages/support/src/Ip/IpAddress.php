<?php

declare(strict_types=1);

namespace Tempest\Support\Ip;

use Stringable;
use Tempest\Support\Comparison\Equable;

/**
 * An IPv4 or IPv6 address.
 *
 * Addresses are compared by their packed representation rather than as strings, so that the notations of the same address are equal.
 *
 * @implements Equable<self|string|null>
 */
final class IpAddress implements Stringable, Equable
{
    /**
     * Whether the address is an IPv4 address.
     */
    public bool $isIpv4 {
        get => strlen($this->bytes) === 4;
    }

    /**
     * Whether the address is an IPv6 address that is not an IPv4-mapped one.
     */
    public bool $isIpv6 {
        get => strlen($this->bytes) === 16;
    }

    /**
     * Whether the address belongs to a range that is not routed on the public internet, such as a loopback or private-use address.
     */
    public bool $isPrivate {
        get => $this->matchesAny(PRIVATE_RANGES);
    }

    private function __construct(
        /**
         * The address as it was given.
         */
        private(set) string $value,
        /**
         * The packed representation of the address. IPv4-mapped IPv6 addresses, such as `::ffff:127.0.0.1`, are narrowed to their IPv4 form.
         *
         * @internal
         */
        private(set) string $bytes,
    ) {}

    /**
     * Creates an address from the given notation, throwing when it is not an address.
     *
     * ### Example
     * ```php
     * IpAddress::from('203.0.113.9');
     * IpAddress::from('2001:db8::1');
     * ```
     *
     * @throws InvalidIpAddress
     */
    public static function from(self|string $ip): self
    {
        return self::tryFrom($ip) ?? throw new InvalidIpAddress((string) $ip);
    }

    /**
     * Creates an address from the given notation, or returns `null` when it is not an address.
     */
    public static function tryFrom(self|string|null $ip): ?self
    {
        if ($ip === null) {
            return null;
        }

        if ($ip instanceof self) {
            return $ip;
        }

        $bytes = self::pack($ip);

        if ($bytes === null) {
            return null;
        }

        return new self($ip, $bytes);
    }

    /**
     * Determines whether the address falls within the given address or CIDR range.
     * Addresses are never matched across families, although IPv4-mapped IPv6 addresses such as `::ffff:127.0.0.1` are compared as IPv4.
     *
     * ### Example
     * ```php
     * IpAddress::from('10.0.1.24')->matches('10.0.0.0/8'); // true
     * IpAddress::from('10.0.1.24')->matches('10.0.1.24'); // true
     * IpAddress::from('10.0.1.24')->matches('::/0'); // false
     * ```
     */
    public function matches(self|string $range): bool
    {
        $range = (string) $range;
        $prefix = null;

        if (str_contains($range, '/')) {
            [$range, $length] = explode('/', $range, limit: 2);

            if (! is_numeric($length)) {
                return false;
            }

            $prefix = (int) $length;
        }

        $subnet = self::pack($range);

        if ($subnet === null) {
            return false;
        }

        // Different lengths indicate different families, which never match.
        if (strlen($this->bytes) !== strlen($subnet)) {
            return false;
        }

        $bits = strlen($subnet) * 8;
        $prefix ??= $bits;

        if ($prefix < 0 || $prefix > $bits) {
            return false;
        }

        $wholeBytes = intdiv($prefix, 8);

        if ($wholeBytes > 0 && substr($this->bytes, 0, $wholeBytes) !== substr($subnet, 0, $wholeBytes)) {
            return false;
        }

        $remainingBits = $prefix % 8;

        if ($remainingBits === 0) {
            return true;
        }

        $mask = chr((0xFF << (8 - $remainingBits)) & 0xFF);

        return ($this->bytes[$wholeBytes] & $mask) === ($subnet[$wholeBytes] & $mask);
    }

    /**
     * Determines whether the address falls within any of the given addresses or CIDR ranges.
     *
     * @param iterable<self|string> $ranges
     */
    public function matchesAny(iterable $ranges): bool
    {
        foreach ($ranges as $range) {
            if ($this->matches($range)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determines whether the given address is the same as this one, regardless of the notation it is written in.
     *
     * ### Example
     * ```php
     * IpAddress::from('::ffff:127.0.0.1')->equals('127.0.0.1'); // true
     * IpAddress::from('2001:db8::1')->equals('2001:0db8:0000:0000:0000:0000:0000:0001'); // true
     * ```
     */
    public function equals(mixed $other): bool
    {
        if (! is_string($other) && ! $other instanceof self) {
            return false;
        }

        return self::tryFrom($other)?->bytes === $this->bytes;
    }

    public function toString(): string
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }

    /**
     * Converts an address to its packed representation, or `null` when it is not an address.
     */
    private static function pack(string $ip): ?string
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
}
