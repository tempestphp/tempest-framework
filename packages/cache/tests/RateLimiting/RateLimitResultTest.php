<?php

declare(strict_types=1);

namespace Tempest\Cache\Tests\RateLimiting;

use PHPUnit\Framework\TestCase;
use Tempest\Cache\RateLimiting\RateLimitResult;

/**
 * @internal
 */
final class RateLimitResultTest extends TestCase
{
    public function test_allow_creates_successful_result(): void
    {
        $resetAt = time() + 60;
        $result = RateLimitResult::allow(limit: 100, remaining: 99, resetAt: $resetAt);

        $this->assertTrue($result->allowed);
        $this->assertSame(100, $result->limit);
        $this->assertSame(99, $result->remaining);
        $this->assertSame($resetAt, $result->resetAt);
        $this->assertLessThanOrEqual(60, $result->retryAfter);
    }

    public function test_deny_creates_failed_result(): void
    {
        $resetAt = time() + 30;
        $result = RateLimitResult::deny(limit: 100, resetAt: $resetAt);

        $this->assertFalse($result->allowed);
        $this->assertSame(100, $result->limit);
        $this->assertSame(0, $result->remaining);
        $this->assertSame($resetAt, $result->resetAt);
        $this->assertLessThanOrEqual(30, $result->retryAfter);
    }

    public function test_retry_after_is_never_negative(): void
    {
        $pastTime = time() - 100;
        $result = RateLimitResult::deny(limit: 100, resetAt: $pastTime);

        $this->assertSame(0, $result->retryAfter);
    }
}
