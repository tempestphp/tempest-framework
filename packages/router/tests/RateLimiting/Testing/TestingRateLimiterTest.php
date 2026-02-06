<?php

declare(strict_types=1);

namespace Tempest\Router\Tests\RateLimiting\Testing;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use Tempest\Router\RateLimiting\Testing\TestingRateLimiter;

/**
 * @internal
 */
final class TestingRateLimiterTest extends TestCase
{
    private TestingRateLimiter $rateLimiter;

    protected function setUp(): void
    {
        $this->rateLimiter = new TestingRateLimiter();
    }

    #[Test]
    public function attempt_allows_within_limit(): void
    {
        $result = $this->rateLimiter->attempt('test-key', maxAttempts: 5, decaySeconds: 60);

        $this->assertTrue($result->allowed);
        $this->assertSame(5, $result->limit);
        $this->assertSame(4, $result->remaining);
    }

    #[Test]
    public function attempt_denies_after_exceeding_limit(): void
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

    #[Test]
    public function attempts_returns_count(): void
    {
        $this->assertSame(0, $this->rateLimiter->attempts('test-key'));

        $this->rateLimiter->attempt('test-key', maxAttempts: 10, decaySeconds: 60);
        $this->assertSame(1, $this->rateLimiter->attempts('test-key'));

        $this->rateLimiter->attempt('test-key', maxAttempts: 10, decaySeconds: 60);
        $this->assertSame(2, $this->rateLimiter->attempts('test-key'));
    }

    #[Test]
    public function remaining_returns_available_attempts(): void
    {
        $this->assertSame(10, $this->rateLimiter->remaining('test-key', maxAttempts: 10));

        $this->rateLimiter->attempt('test-key', maxAttempts: 10, decaySeconds: 60);
        $this->assertSame(9, $this->rateLimiter->remaining('test-key', maxAttempts: 10));
    }

    #[Test]
    public function too_many_attempts_returns_true_when_exceeded(): void
    {
        $this->assertFalse($this->rateLimiter->tooManyAttempts('test-key', maxAttempts: 2));

        $this->rateLimiter->attempt('test-key', maxAttempts: 2, decaySeconds: 60);
        $this->assertFalse($this->rateLimiter->tooManyAttempts('test-key', maxAttempts: 2));

        $this->rateLimiter->attempt('test-key', maxAttempts: 2, decaySeconds: 60);
        $this->assertTrue($this->rateLimiter->tooManyAttempts('test-key', maxAttempts: 2));
    }

    #[Test]
    public function clear_removes_attempts_for_key(): void
    {
        $this->rateLimiter->attempt('test-key', maxAttempts: 5, decaySeconds: 60);
        $this->assertSame(1, $this->rateLimiter->attempts('test-key'));

        $this->rateLimiter->clear('test-key');
        $this->assertSame(0, $this->rateLimiter->attempts('test-key'));
    }

    #[Test]
    public function clear_all_removes_all_state(): void
    {
        $this->rateLimiter->attempt('key-1', maxAttempts: 5, decaySeconds: 60);
        $this->rateLimiter->attempt('key-2', maxAttempts: 5, decaySeconds: 60);

        $this->rateLimiter->clearAll();

        $this->assertSame(0, $this->rateLimiter->attempts('key-1'));
        $this->assertSame(0, $this->rateLimiter->attempts('key-2'));
    }

    #[Test]
    public function assert_attempts_passes_when_correct(): void
    {
        $this->rateLimiter->attempt('test-key', maxAttempts: 5, decaySeconds: 60);
        $this->rateLimiter->attempt('test-key', maxAttempts: 5, decaySeconds: 60);

        $result = $this->rateLimiter->assertAttempts('test-key', 2);

        $this->assertSame($this->rateLimiter, $result);
    }

    #[Test]
    public function assert_attempts_fails_when_incorrect(): void
    {
        $this->rateLimiter->attempt('test-key', maxAttempts: 5, decaySeconds: 60);

        $this->expectException(ExpectationFailedException::class);
        $this->rateLimiter->assertAttempts('test-key', 5);
    }

    #[Test]
    public function assert_remaining_attempts_passes_when_correct(): void
    {
        $this->rateLimiter->attempt('test-key', maxAttempts: 5, decaySeconds: 60);

        $result = $this->rateLimiter->assertRemainingAttempts('test-key', maxAttempts: 5, expected: 4);

        $this->assertSame($this->rateLimiter, $result);
    }

    #[Test]
    public function assert_remaining_attempts_fails_when_incorrect(): void
    {
        $this->rateLimiter->attempt('test-key', maxAttempts: 5, decaySeconds: 60);

        $this->expectException(ExpectationFailedException::class);
        $this->rateLimiter->assertRemainingAttempts('test-key', maxAttempts: 5, expected: 5);
    }

    #[Test]
    public function assert_limited_passes_when_limited(): void
    {
        for ($i = 0; $i < 3; $i++) {
            $this->rateLimiter->attempt('test-key', maxAttempts: 3, decaySeconds: 60);
        }

        $result = $this->rateLimiter->assertLimited('test-key', maxAttempts: 3);

        $this->assertSame($this->rateLimiter, $result);
    }

    #[Test]
    public function assert_limited_fails_when_not_limited(): void
    {
        $this->rateLimiter->attempt('test-key', maxAttempts: 5, decaySeconds: 60);

        $this->expectException(ExpectationFailedException::class);
        $this->rateLimiter->assertLimited('test-key', maxAttempts: 5);
    }

    #[Test]
    public function assert_not_limited_passes_when_not_limited(): void
    {
        $this->rateLimiter->attempt('test-key', maxAttempts: 5, decaySeconds: 60);

        $result = $this->rateLimiter->assertNotLimited('test-key', maxAttempts: 5);

        $this->assertSame($this->rateLimiter, $result);
    }

    #[Test]
    public function assert_not_limited_fails_when_limited(): void
    {
        for ($i = 0; $i < 3; $i++) {
            $this->rateLimiter->attempt('test-key', maxAttempts: 3, decaySeconds: 60);
        }

        $this->expectException(ExpectationFailedException::class);
        $this->rateLimiter->assertNotLimited('test-key', maxAttempts: 3);
    }

    #[Test]
    public function assert_cleared_passes_when_cleared(): void
    {
        $this->rateLimiter->attempt('test-key', maxAttempts: 5, decaySeconds: 60);
        $this->rateLimiter->clear('test-key');

        $result = $this->rateLimiter->assertCleared('test-key');

        $this->assertSame($this->rateLimiter, $result);
    }

    #[Test]
    public function assert_cleared_fails_when_not_cleared(): void
    {
        $this->rateLimiter->attempt('test-key', maxAttempts: 5, decaySeconds: 60);

        $this->expectException(ExpectationFailedException::class);
        $this->rateLimiter->assertCleared('test-key');
    }

    #[Test]
    public function assert_empty_passes_when_empty(): void
    {
        $result = $this->rateLimiter->assertEmpty();

        $this->assertSame($this->rateLimiter, $result);
    }

    #[Test]
    public function assert_empty_fails_when_not_empty(): void
    {
        $this->rateLimiter->attempt('test-key', maxAttempts: 5, decaySeconds: 60);

        $this->expectException(ExpectationFailedException::class);
        $this->rateLimiter->assertEmpty();
    }

    #[Test]
    public function assert_not_empty_passes_when_not_empty(): void
    {
        $this->rateLimiter->attempt('test-key', maxAttempts: 5, decaySeconds: 60);

        $result = $this->rateLimiter->assertNotEmpty();

        $this->assertSame($this->rateLimiter, $result);
    }

    #[Test]
    public function assert_not_empty_fails_when_empty(): void
    {
        $this->expectException(ExpectationFailedException::class);
        $this->rateLimiter->assertNotEmpty();
    }

    #[Test]
    public function assert_has_key_passes_when_key_exists(): void
    {
        $this->rateLimiter->attempt('test-key', maxAttempts: 5, decaySeconds: 60);

        $result = $this->rateLimiter->assertHasKey('test-key');

        $this->assertSame($this->rateLimiter, $result);
    }

    #[Test]
    public function assert_has_key_fails_when_key_missing(): void
    {
        $this->expectException(ExpectationFailedException::class);
        $this->rateLimiter->assertHasKey('missing-key');
    }

    #[Test]
    public function assert_missing_key_passes_when_key_missing(): void
    {
        $result = $this->rateLimiter->assertMissingKey('missing-key');

        $this->assertSame($this->rateLimiter, $result);
    }

    #[Test]
    public function assert_missing_key_fails_when_key_exists(): void
    {
        $this->rateLimiter->attempt('test-key', maxAttempts: 5, decaySeconds: 60);

        $this->expectException(ExpectationFailedException::class);
        $this->rateLimiter->assertMissingKey('test-key');
    }

    #[Test]
    public function assertions_can_be_chained(): void
    {
        $this->rateLimiter->attempt('test-key', maxAttempts: 5, decaySeconds: 60);
        $this->rateLimiter->attempt('test-key', maxAttempts: 5, decaySeconds: 60);

        $result = $this->rateLimiter
            ->assertNotEmpty()
            ->assertHasKey('test-key')
            ->assertAttempts('test-key', 2)
            ->assertRemainingAttempts('test-key', maxAttempts: 5, expected: 3)
            ->assertNotLimited('test-key', maxAttempts: 5);

        $this->assertSame($this->rateLimiter, $result);
    }
}
