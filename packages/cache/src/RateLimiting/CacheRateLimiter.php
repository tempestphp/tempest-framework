<?php

declare(strict_types=1);

namespace Tempest\Cache\RateLimiting;

use Tempest\Cache\Cache;
use Tempest\DateTime\DateTime;
use Tempest\DateTime\Duration;

/**
 * A rate limiter implementation using the cache.
 */
final readonly class CacheRateLimiter implements RateLimiter
{
    private const string PREFIX = 'tempest_rate_limit_';

    public function __construct(
        private Cache $cache,
    ) {}

    public function attempt(string $key, int $maxAttempts, int $decaySeconds): RateLimitResult
    {
        $cacheKey = $this->getCacheKey($key);
        $timerKey = $this->getTimerKey($key);

        // Get or set the timer (when the window ends)
        $resetAt = (int) $this->cache->get($timerKey);

        if ($resetAt === 0) {
            $resetAt = time() + $decaySeconds;
            $this->cache->put($timerKey, $resetAt, Duration::seconds($decaySeconds));
        }

        // Increment the counter
        $attempts = $this->cache->increment($cacheKey);

        // Set expiration on first hit
        if ($attempts === 1) {
            $this->cache->put($cacheKey, 1, Duration::seconds($decaySeconds));
        }

        $remaining = max(0, $maxAttempts - $attempts);

        if ($attempts > $maxAttempts) {
            return RateLimitResult::deny($maxAttempts, (int) $resetAt);
        }

        return RateLimitResult::allow($maxAttempts, $remaining, $resetAt);
    }

    public function attempts(string $key): int
    {
        return (int) ($this->cache->get($this->getCacheKey($key)) ?? 0);
    }

    public function remaining(string $key, int $maxAttempts): int
    {
        return max(0, $maxAttempts - $this->attempts($key));
    }

    public function tooManyAttempts(string $key, int $maxAttempts): bool
    {
        return $this->attempts($key) >= $maxAttempts;
    }

    public function clear(string $key): void
    {
        $this->cache->remove($this->getCacheKey($key));
        $this->cache->remove($this->getTimerKey($key));
    }

    public function availableAt(string $key): DateTime
    {
        $timestamp = (int) ($this->cache->get($this->getTimerKey($key)) ?? time());

        return DateTime::fromTimestamp($timestamp);
    }

    private function getCacheKey(string $key): string
    {
        return self::PREFIX . $this->sanitizeKey($key);
    }

    private function getTimerKey(string $key): string
    {
        return self::PREFIX . $this->sanitizeKey($key) . '_timer';
    }

    /**
     * Sanitize a cache key to be compatible with all cache adapters.
     * Replaces reserved characters with underscores.
     */
    private function sanitizeKey(string $key): string
    {
        return preg_replace('/[{}()\/@:\\\\]/', '_', $key);
    }
}
