<?php

declare(strict_types=1);

namespace Tempest\Router\Tests\RateLimiting;

use PHPUnit\Framework\TestCase;
use Tempest\Cache\Testing\TestingCache;
use Tempest\Clock\GenericClock;
use Tempest\Router\RateLimiting\CacheRateLimiter;

/**
 * @internal
 */
final class CacheRateLimiterTest extends TestCase
{
    private TestingCache $cache;

    private CacheRateLimiter $rateLimiter;

    protected function setUp(): void
    {
        $clock = new GenericClock();
        $this->cache = new TestingCache(tag: 'rate-limit-test', clock: $clock->toPsrClock());
        $this->rateLimiter = new CacheRateLimiter($this->cache);
    }

    public function test_attempt_allows_within_limit(): void
    {
        $result = $this->rateLimiter->attempt('test-key', maxAttempts: 5, decaySeconds: 60);

        $this->assertTrue($result->allowed);
        $this->assertSame(5, $result->limit);
        $this->assertSame(4, $result->remaining);
    }

    public function test_attempt_denies_after_exceeding_limit(): void
    {
        // Make 5 attempts (the limit)
        for ($i = 0; $i < 5; $i++) {
            $this->rateLimiter->attempt('test-key', maxAttempts: 5, decaySeconds: 60);
        }

        // 6th attempt should be denied
        $result = $this->rateLimiter->attempt('test-key', maxAttempts: 5, decaySeconds: 60);

        $this->assertFalse($result->allowed);
        $this->assertSame(0, $result->remaining);
    }

    public function test_attempts_returns_count(): void
    {
        $this->assertSame(0, $this->rateLimiter->attempts('test-key'));

        $this->rateLimiter->attempt('test-key', maxAttempts: 10, decaySeconds: 60);
        $this->assertSame(1, $this->rateLimiter->attempts('test-key'));

        $this->rateLimiter->attempt('test-key', maxAttempts: 10, decaySeconds: 60);
        $this->assertSame(2, $this->rateLimiter->attempts('test-key'));
    }

    public function test_remaining_returns_available_attempts(): void
    {
        $this->assertSame(10, $this->rateLimiter->remaining('test-key', maxAttempts: 10));

        $this->rateLimiter->attempt('test-key', maxAttempts: 10, decaySeconds: 60);
        $this->assertSame(9, $this->rateLimiter->remaining('test-key', maxAttempts: 10));
    }

    public function test_too_many_attempts_returns_true_when_exceeded(): void
    {
        $this->assertFalse($this->rateLimiter->tooManyAttempts('test-key', maxAttempts: 2));

        $this->rateLimiter->attempt('test-key', maxAttempts: 2, decaySeconds: 60);
        $this->assertFalse($this->rateLimiter->tooManyAttempts('test-key', maxAttempts: 2));

        $this->rateLimiter->attempt('test-key', maxAttempts: 2, decaySeconds: 60);
        $this->assertTrue($this->rateLimiter->tooManyAttempts('test-key', maxAttempts: 2));
    }

    public function test_clear_resets_the_counter(): void
    {
        $this->rateLimiter->attempt('test-key', maxAttempts: 5, decaySeconds: 60);
        $this->rateLimiter->attempt('test-key', maxAttempts: 5, decaySeconds: 60);

        $this->assertSame(2, $this->rateLimiter->attempts('test-key'));

        $this->rateLimiter->clear('test-key');

        $this->assertSame(0, $this->rateLimiter->attempts('test-key'));
    }

    public function test_different_keys_are_independent(): void
    {
        $this->rateLimiter->attempt('key-a', maxAttempts: 2, decaySeconds: 60);
        $this->rateLimiter->attempt('key-a', maxAttempts: 2, decaySeconds: 60);

        // key-a is at limit
        $this->assertTrue($this->rateLimiter->tooManyAttempts('key-a', maxAttempts: 2));

        // key-b should still be available
        $this->assertFalse($this->rateLimiter->tooManyAttempts('key-b', maxAttempts: 2));
    }

    public function test_reset_time_is_set_correctly(): void
    {
        $beforeTime = time();
        $result = $this->rateLimiter->attempt('test-key', maxAttempts: 5, decaySeconds: 60);
        $afterTime = time();

        // Reset time should be approximately 60 seconds from now
        $this->assertGreaterThanOrEqual($beforeTime + 60, $result->resetAt);
        $this->assertLessThanOrEqual($afterTime + 60, $result->resetAt);
    }
}
