<?php

declare(strict_types=1);

namespace Tempest\RateLimit\Config;

use Tempest\Cache\Cache;
use Tempest\Clock\Clock;
use Tempest\Container\Container;
use Tempest\RateLimit\Http\ClientIpKeyResolver;
use Tempest\RateLimit\Http\RateLimitKeyResolver;
use Tempest\RateLimit\Storage\CacheRateLimitStorage;

/**
 * Stores rate limit windows in the cache. This works anywhere a cache is configured, but takes a lock
 * on every increment. {@see RedisRateLimitConfig} counts atomically and is recommended in production.
 */
final class CacheRateLimitConfig implements RateLimitConfig
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
         * Lock timeout for concurrent updates.
         */
        public int $lockTimeoutInSeconds = 5,

        /**
         * Whether HTTP responses include `X-RateLimit-*` headers. These headers are per-client and must
         * not be cached by a shared proxy.
         */
        public bool $includeHeaders = true,

        /** @var class-string<RateLimitKeyResolver> */
        public string $keyResolverClass = ClientIpKeyResolver::class,
    ) {}

    public function storageKey(string $key): string
    {
        return $this->keyPrefix . '_' . hash('xxh128', $key);
    }

    public function createStorage(Container $container): CacheRateLimitStorage
    {
        return new CacheRateLimitStorage(
            cache: $container->get(Cache::class),
            clock: $container->get(Clock::class),
            config: $this,
        );
    }
}
