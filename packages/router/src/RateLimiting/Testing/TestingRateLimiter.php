<?php

declare(strict_types=1);

namespace Tempest\Router\RateLimiting\Testing;

use Tempest\Router\RateLimiting\RateLimiter;
use Tempest\Router\RateLimiting\RateLimitResult;

/**
 * An in-memory rate limiter for testing purposes.
 */
final class TestingRateLimiter implements RateLimiter
{
    /** @var array<string, int> */
    private array $attempts = [];

    /** @var array<string, int> */
    private array $timers = [];

    public function attempt(string $key, int $maxAttempts, int $decaySeconds): RateLimitResult
    {
        // Initialize timer if not set
        if (! isset($this->timers[$key])) {
            $this->timers[$key] = time() + $decaySeconds;
        }

        // Increment attempts
        if (! isset($this->attempts[$key])) {
            $this->attempts[$key] = 0;
        }
        $this->attempts[$key]++;

        $attempts = $this->attempts[$key];
        $resetAt = $this->timers[$key];
        $remaining = max(0, $maxAttempts - $attempts);

        if ($attempts > $maxAttempts) {
            return RateLimitResult::deny($maxAttempts, $resetAt);
        }

        return RateLimitResult::allow($maxAttempts, $remaining, $resetAt);
    }

    public function attempts(string $key): int
    {
        return $this->attempts[$key] ?? 0;
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
        unset($this->attempts[$key], $this->timers[$key]);
    }

    public function availableAt(string $key): int
    {
        return $this->timers[$key] ?? time();
    }

    /**
     * Clear all rate limiting state.
     */
    public function clearAll(): void
    {
        $this->attempts = [];
        $this->timers = [];
    }
}
