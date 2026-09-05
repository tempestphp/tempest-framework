<?php

declare(strict_types=1);

namespace Tempest\RateLimit\Config;

use Tempest\Clock\Clock;
use Tempest\Container\Container;
use Tempest\KeyValue\Redis\Redis;
use Tempest\RateLimit\Http\ClientIPKeyResolver;
use Tempest\RateLimit\Http\RateLimitKeyResolver;
use Tempest\RateLimit\Storage\RedisRateLimitStorage;

/**
 * Stores rate limit windows in Redis, counting atomically instead of taking a lock on every increment.
 */
final class RedisRateLimitConfig implements RateLimitConfig
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

    public function createStorage(Container $container): RedisRateLimitStorage
    {
        return new RedisRateLimitStorage(
            redis: $container->get(Redis::class),
            clock: $container->get(Clock::class),
            config: $this,
        );
    }
}
