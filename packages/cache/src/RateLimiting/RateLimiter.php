<?php

declare(strict_types=1);

namespace Tempest\Cache\RateLimiting;

use Tempest\DateTime\DateTime;

/**
 * A rate limiter tracks and enforces request limits.
 */
interface RateLimiter
{
    /**
     * Attempt to hit the rate limiter for the given key.
     *
     * @param string $key The unique identifier for this rate limit bucket.
     * @param int $maxAttempts The maximum number of attempts allowed.
     * @param int $decaySeconds The time window in seconds.
     */
    public function attempt(string $key, int $maxAttempts, int $decaySeconds): RateLimitResult;

    /**
     * Get the number of attempts for the given key.
     */
    public function attempts(string $key): int;

    /**
     * Get the number of remaining attempts for the given key.
     */
    public function remaining(string $key, int $maxAttempts): int;

    /**
     * Determine if the given key has been hit too many times.
     */
    public function tooManyAttempts(string $key, int $maxAttempts): bool;

    /**
     * Clear the rate limiter for the given key.
     */
    public function clear(string $key): void;

    /**
     * Get the date-time when the rate limit resets for the given key.
     */
    public function availableAt(string $key): DateTime;
}
