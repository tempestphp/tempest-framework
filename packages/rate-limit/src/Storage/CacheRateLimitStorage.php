<?php

declare(strict_types=1);

namespace Tempest\RateLimit\Storage;

use Tempest\Cache\Cache;
use Tempest\Cache\LockAcquisitionTimedOut;
use Tempest\Clock\Clock;
use Tempest\DateTime\Duration;
use Tempest\RateLimit\Config\CacheRateLimitConfig;
use Tempest\RateLimit\RateLimitStorage;

/**
 * Stores rate limit windows in the cache. Counters are only as durable as the cache itself.
 */
final readonly class CacheRateLimitStorage implements RateLimitStorage
{
    public function __construct(
        private Cache $cache,
        private Clock $clock,
        private CacheRateLimitConfig $config,
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

        try {
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
                wait: Duration::milliseconds($this->config->lockWaitInMilliseconds),
            );
        } catch (LockAcquisitionTimedOut $timeout) {
            // The counter could not be read, so the attempt has no outcome. Reporting one either way
            // would be a guess: a window that is not open yet looks the same as an exhausted one.
            throw new RateLimitStorageFailed($key, 'the counter was locked by another process', previous: $timeout);
        }
    }

    public function remove(string $key): void
    {
        $this->cache->remove($this->config->storageKey($key));
    }
}
