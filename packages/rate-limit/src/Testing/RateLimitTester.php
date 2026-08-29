<?php

declare(strict_types=1);

namespace Tempest\RateLimit\Testing;

use PHPUnit\Framework\Assert;
use Tempest\Clock\Clock;
use Tempest\Container\Container;
use Tempest\RateLimit\Config\RateLimitConfig;
use Tempest\RateLimit\GenericRateLimiter;
use Tempest\RateLimit\RateLimit;
use Tempest\RateLimit\RateLimiter;
use Tempest\RateLimit\RateLimitStorage;

final readonly class RateLimitTester
{
    public function __construct(
        private Container $container,
    ) {}

    /**
     * Swaps the configured storage for an in-memory one, which needs no external infrastructure and
     * cannot leak between tests. Calling this again discards every attempt recorded so far.
     */
    public function fake(): self
    {
        $storage = new TestingRateLimitStorage(
            clock: $this->container->get(Clock::class),
        );

        $this->container->singleton(RateLimitStorage::class, $storage);

        // The limiter holds on to the storage it was built with. It's rebuilt around the new one.
        $this->container->singleton(RateLimiter::class, new GenericRateLimiter(
            storage: $storage,
            clock: $this->container->get(Clock::class),
        ));

        return $this;
    }

    /**
     * Leaves routes decorated with {@see \Tempest\RateLimit\Http\Throttle} unlimited. Limits consumed
     * directly through {@see RateLimiter} are not affected.
     */
    public function preventThrottling(): self
    {
        $this->container->get(RateLimitConfig::class)->enabled = false;

        return $this;
    }

    /**
     * Applies the limits declared by {@see \Tempest\RateLimit\Http\Throttle} again, undoing {@see self::preventThrottling()}.
     */
    public function allowThrottling(): self
    {
        $this->container->get(RateLimitConfig::class)->enabled = true;

        return $this;
    }

    /**
     * Records attempts against the specified rate limit, as though a client had made them. The window
     * is incremented once by `$times`, since only the first attempt decides when the window ends.
     */
    public function hit(RateLimit $limit, int $times = 1): self
    {
        $this->limiter()->attempt($limit, by: $times);

        return $this;
    }

    /**
     * Records as many attempts as the specified rate limit allows, leaving it with no allowance left.
     */
    public function exhaust(RateLimit $limit): self
    {
        return $this->hit($limit, $limit->attempts);
    }

    /**
     * Discards the attempts recorded for the specified rate limit.
     */
    public function clear(RateLimit $limit): self
    {
        $this->limiter()->clear($limit);

        return $this;
    }

    /**
     * Asserts that the specified rate limit has no allowance left.
     */
    public function assertThrottled(RateLimit $limit): self
    {
        Assert::assertTrue(
            condition: $this->limiter()->peek($limit)->exceeded,
            message: "The rate limit for `{$limit->key}` was expected to be exceeded, but it was not.",
        );

        return $this;
    }

    /**
     * Asserts that the specified rate limit still has allowance left.
     */
    public function assertNotThrottled(RateLimit $limit): self
    {
        Assert::assertFalse(
            condition: $this->limiter()->peek($limit)->exceeded,
            message: "The rate limit for `{$limit->key}` was expected not to be exceeded, but it was.",
        );

        return $this;
    }

    /**
     * Asserts how many attempts have been recorded against the specified rate limit.
     */
    public function assertHits(RateLimit $limit, int $expected): self
    {
        Assert::assertSame(
            expected: $expected,
            actual: $hits = $this->limiter()->peek($limit)->hits,
            message: "The rate limit for `{$limit->key}` was expected to have {$expected} attempt(s) recorded, {$hits} found.",
        );

        return $this;
    }

    /**
     * Asserts how many attempts the specified rate limit has left.
     */
    public function assertRemaining(RateLimit $limit, int $expected): self
    {
        Assert::assertSame(
            expected: $expected,
            actual: $remaining = $this->limiter()->peek($limit)->remaining,
            message: "The rate limit for `{$limit->key}` was expected to have {$expected} attempt(s) left, {$remaining} found.",
        );

        return $this;
    }

    private function limiter(): RateLimiter
    {
        return $this->container->get(RateLimiter::class);
    }
}
