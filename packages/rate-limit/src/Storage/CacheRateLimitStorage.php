<?php

declare(strict_types=1);

namespace Tempest\RateLimit\Storage;

use Tempest\Cache\Cache;
use Tempest\Clock\Clock;
use Tempest\DateTime\Duration;
use Tempest\RateLimit\Config\RateLimitConfig;
use Tempest\RateLimit\RateLimitStorage;

/**
 * Stores rate limit windows in the cache. Counters are only as durable as the cache itself.
 */
final readonly class CacheRateLimitStorage implements RateLimitStorage
{
    public function __construct(
        private Cache $cache,
        private Clock $clock,
        private RateLimitConfig $config,
    ) {}

    public function find(string $key): ?RateLimitState
    {
        $state = $this->cache->get($this->config->storageKey($key));

        if (! $state instanceof RateLimitState) {
            return null;
        }

        // Cache expiry may drift from the clock's time.
        if ($state->resetsAtInSeconds <= $this->clock->seconds()) {
            return null;
        }

        return $state;
    }

    public function increment(string $key, Duration $window, int $by = 1): RateLimitState
    {
        $lock = $this->cache->lock(
            key: $this->config->storageKey($key) . '_lock',
            duration: Duration::seconds($this->config->lockTimeoutInSeconds),
        );

        return $lock->execute(
            callback: function () use ($key, $window, $by): RateLimitState {
                $state = $this->find($key)?->incrementedBy($by) ?? RateLimitState::opening($this->clock, $window, hits: $by);

                $this->cache->put(
                    key: $this->config->storageKey($key),
                    value: $state,
                    expiration: Duration::seconds(max(1, $state->resetsAtInSeconds - $this->clock->seconds())),
                );

                return $state;
            },
            wait: Duration::seconds($this->config->lockTimeoutInSeconds),
        );
    }

    public function remove(string $key): void
    {
        $this->cache->remove($this->config->storageKey($key));
    }
}
