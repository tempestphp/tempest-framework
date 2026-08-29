<?php

declare(strict_types=1);

namespace Tempest\RateLimit\Config;

use Tempest\RateLimit\Http\ClientIpKeyResolver;
use Tempest\RateLimit\Http\RateLimitKeyResolver;
use Tempest\RateLimit\RateLimitStorage;
use Tempest\RateLimit\Storage\CacheRateLimitStorage;

final class RateLimitConfig
{
    public function __construct(
        /**
         * Whether `#[Throttle]` applies the limits it declares. Limits consumed directly through
         * {@see \Tempest\RateLimit\RateLimiter} are not affected.
         */
        public bool $enabled = true,

        /**
         * Prefix used for the keys under which rate limit windows are stored.
         */
        public string $keyPrefix = 'rate_limit',

        /**
         * Lock timeout for concurrent updates. Used by {@see \Tempest\RateLimit\Storage\CacheRateLimitStorage}.
         */
        public int $lockTimeoutInSeconds = 5,

        /**
         * Whether HTTP responses include `X-RateLimit-*` headers. These headers are per-client and must
         * not be cached by a shared proxy.
         */
        public bool $includeHeaders = true,

        /**
         * Storage for rate limit counters. The default works anywhere a cache is configured, but takes a
         * lock on every increment. {@see \Tempest\RateLimit\Storage\RedisRateLimitStorage} counts
         * atomically and is recommended in production.
         *
         * @var class-string<RateLimitStorage>
         */
        public string $storageClass = CacheRateLimitStorage::class,

        /** @var class-string<RateLimitKeyResolver> */
        public string $keyResolverClass = ClientIpKeyResolver::class,
    ) {}

    /**
     * Returns the key a rate limit's window is stored under. Keys are hashed, since a limit may be
     * scoped to arbitrary input that the store would not accept as a key.
     */
    public function storageKey(string $key): string
    {
        return $this->keyPrefix . '_' . hash('xxh128', $key);
    }
}
