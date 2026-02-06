<?php

declare(strict_types=1);

namespace Tempest\Router\RateLimiting\Testing;

use PHPUnit\Framework\Assert;
use Tempest\Cache\RateLimiting\RateLimiter;
use Tempest\Cache\RateLimiting\RateLimitResult;
use Tempest\DateTime\DateTime;

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

    public function availableAt(string $key): DateTime
    {
        $timestamp = $this->timers[$key] ?? time();

        return DateTime::fromTimestamp($timestamp);
    }

    /**
     * Clear all rate limiting state.
     */
    public function clearAll(): void
    {
        $this->attempts = [];
        $this->timers = [];
    }

    /**
     * Assert that a key has been hit a specific number of times.
     */
    public function assertAttempts(string $key, int $expected): self
    {
        Assert::assertSame(
            $expected,
            $this->attempts($key),
            sprintf('Expected %d attempts for key `%s`, got %d.', $expected, $key, $this->attempts($key)),
        );

        return $this;
    }

    /**
     * Assert that a key has remaining attempts.
     */
    public function assertRemainingAttempts(string $key, int $maxAttempts, int $expected): self
    {
        Assert::assertSame(
            $expected,
            $this->remaining($key, $maxAttempts),
            sprintf('Expected %d remaining attempts for key `%s`, got %d.', $expected, $key, $this->remaining($key, $maxAttempts)),
        );

        return $this;
    }

    /**
     * Assert that a key is rate limited (has exceeded max attempts).
     */
    public function assertLimited(string $key, int $maxAttempts): self
    {
        Assert::assertTrue(
            $this->tooManyAttempts($key, $maxAttempts),
            sprintf('Expected key `%s` to be rate limited with max %d attempts, but it has %d attempts.', $key, $maxAttempts, $this->attempts($key)),
        );

        return $this;
    }

    /**
     * Assert that a key is not rate limited.
     */
    public function assertNotLimited(string $key, int $maxAttempts): self
    {
        Assert::assertFalse(
            $this->tooManyAttempts($key, $maxAttempts),
            sprintf('Expected key `%s` to not be rate limited, but it has %d attempts (max: %d).', $key, $this->attempts($key), $maxAttempts),
        );

        return $this;
    }

    /**
     * Assert that a key has no recorded attempts (or has been cleared).
     */
    public function assertCleared(string $key): self
    {
        Assert::assertFalse(
            isset($this->attempts[$key]),
            sprintf('Expected key `%s` to be cleared, but it has %d attempts.', $key, $this->attempts($key)),
        );

        return $this;
    }

    /**
     * Assert that no rate limiting state exists.
     */
    public function assertEmpty(): self
    {
        Assert::assertEmpty(
            $this->attempts,
            sprintf('Expected rate limiter to be empty, but it has %d keys.', count($this->attempts)),
        );

        return $this;
    }

    /**
     * Assert that some rate limiting state exists.
     */
    public function assertNotEmpty(): self
    {
        Assert::assertNotEmpty(
            $this->attempts,
            'Expected rate limiter to have some state, but it is empty.',
        );

        return $this;
    }

    /**
     * Assert that a specific key exists in the rate limiter.
     */
    public function assertHasKey(string $key): self
    {
        Assert::assertArrayHasKey(
            $key,
            $this->attempts,
            sprintf('Expected rate limiter to have key `%s`, but it does not.', $key),
        );

        return $this;
    }

    /**
     * Assert that a specific key does not exist in the rate limiter.
     */
    public function assertMissingKey(string $key): self
    {
        Assert::assertArrayNotHasKey(
            $key,
            $this->attempts,
            sprintf('Expected rate limiter to not have key `%s`, but it does.', $key),
        );

        return $this;
    }
}
