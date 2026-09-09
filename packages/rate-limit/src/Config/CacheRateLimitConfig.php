<?php

declare(strict_types=1);

namespace Tempest\RateLimit\Config;

use Tempest\Cache\Cache;
use Tempest\Clock\Clock;
use Tempest\Container\Container;
use Tempest\RateLimit\Http\ClientIPKeyResolver;
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
         * Prefix used for the keys under which rate limit windows are stored.
         */
        public string $keyPrefix = 'rate_limit',

        /**
         * How long a lock on a counter is held before it is considered abandoned. This only has to
         * outlast a single update.
         */
        public int $lockTimeoutInSeconds = 5,

        /**
         * How long to wait for a counter locked by another process. Requests for one counter are
         * serialized, so this is the delay a client may add to its own requests before being turned
         * away. Waiting longer holds a worker for longer, which is the opposite of what a limit is
         * for.
         */
        public int $lockWaitInMilliseconds = 250,

        /**
         * Whether HTTP responses include `X-RateLimit-*` headers. These headers are per-client and must
         * not be cached by a shared proxy.
         */
        public bool $includeHeaders = true,

        /** @var class-string<RateLimitKeyResolver> */
        public string $keyResolverClass = ClientIPKeyResolver::class,
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
