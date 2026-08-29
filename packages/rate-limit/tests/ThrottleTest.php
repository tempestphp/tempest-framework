<?php

declare(strict_types=1);

namespace Tempest\RateLimit\Tests;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\RateLimit\Http\Throttle;
use Tempest\RateLimit\Per;

/**
 * @internal
 */
final class ThrottleTest extends TestCase
{
    #[Test]
    public function the_described_limit_spans_the_declared_window(): void
    {
        $limit = new Throttle(attempts: 10, per: Per::MINUTE, every: 5)->toRateLimit();

        $this->assertSame(10, $limit->attempts);
        $this->assertSame(300.0, $limit->window->getTotalSeconds());
    }

    #[Test]
    public function the_window_defaults_to_a_single_minute(): void
    {
        $limit = new Throttle(attempts: 10)->toRateLimit();

        $this->assertSame(60.0, $limit->window->getTotalSeconds());
    }

    #[Test]
    public function a_named_bucket_becomes_the_limits_key(): void
    {
        $limit = new Throttle(attempts: 10, bucket: 'api')->toRateLimit();

        $this->assertSame('api', $limit->key);
    }

    #[Test]
    public function an_unnamed_bucket_leaves_the_limit_unkeyed(): void
    {
        $limit = new Throttle(attempts: 10)->toRateLimit();

        $this->assertNull($limit->key);
    }
}
