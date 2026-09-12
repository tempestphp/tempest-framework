<?php

declare(strict_types=1);

namespace Tempest\RateLimit\Tests;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\RateLimit\Http\Throttle;
use Tempest\RateLimit\Per;
use Tempest\RateLimit\Http\RateLimitProfile;

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
    public function a_named_bucket_stays_off_the_limit(): void
    {
        $throttle = new Throttle(attempts: 10, bucket: 'api');

        // Keeping the bucket off the limit is what tells it apart from an application's own key.
        $this->assertSame('api', $throttle->bucket);
        $this->assertNull($throttle->toRateLimit()->key);
    }

    #[Test]
    public function an_unnamed_bucket_leaves_the_limit_unkeyed(): void
    {
        $limit = new Throttle(attempts: 10)->toRateLimit();

        $this->assertNull($limit->key);
    }

    #[Test]
    public function a_profile_can_replace_a_static_limit(): void
    {
        $throttle = new Throttle(profile: RateLimitProfile::class);

        $this->assertSame(RateLimitProfile::class, $throttle->profile);
        $this->assertNull($throttle->attempts);
    }

    #[Test]
    public function a_profile_cannot_be_combined_with_a_static_limit(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Throttle(attempts: 10, profile: RateLimitProfile::class);
    }

    #[Test]
    public function a_throttle_must_define_a_limit_or_profile(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Throttle();
    }
}
