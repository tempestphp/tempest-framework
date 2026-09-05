<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\RateLimit;

use PHPUnit\Framework\Attributes\Test;
use Tempest\Clock\MockClock;
use Tempest\DateTime\Duration;
use Tempest\RateLimit\RateLimit;
use Tempest\RateLimit\RateLimiter;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 */
final class RateLimitTesterTest extends FrameworkIntegrationTestCase
{
    private MockClock $clock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->clock = $this->clock('2025-08-02 12:00:00');
        $this->rateLimit->fake();
    }

    #[Test]
    public function attempts_are_counted_without_a_cache_or_a_redis_server(): void
    {
        $limit = RateLimit::perMinute(3)->withKey('login');

        $this->rateLimit
            ->assertNotThrottled($limit)
            ->hit($limit, times: 2)
            ->assertHits($limit, 2)
            ->assertRemaining($limit, 1)
            ->assertNotThrottled($limit);
    }

    #[Test]
    public function preventing_throttling_leaves_limits_untouched(): void
    {
        $limit = RateLimit::perMinute(3)->withKey('login');

        $this->rateLimit
            ->exhaust($limit)
            ->assertThrottled($limit)
            ->preventThrottling()
            ->hit($limit, times: 10)
            ->assertNotThrottled($limit)
            ->allowThrottling()
            ->assertThrottled($limit)
            ->assertHits($limit, 3);
    }

    #[Test]
    public function preventing_throttling_lets_an_exhausted_limit_run_its_callback(): void
    {
        $limit = RateLimit::perMinute(1)->withKey('login');

        $this->rateLimit->exhaust($limit)->preventThrottling();

        $this->assertSame(
            expected: 'executed',
            actual: $this->container->get(RateLimiter::class)->throttle($limit, fn () => 'executed'),
        );
    }

    #[Test]
    public function a_limit_may_be_exhausted_and_cleared(): void
    {
        $limit = RateLimit::perMinute(3)->withKey('login');

        $this->rateLimit
            ->exhaust($limit)
            ->assertThrottled($limit)
            ->clear($limit)
            ->assertNotThrottled($limit)
            ->assertHits($limit, 0);
    }

    #[Test]
    public function counters_are_kept_apart_per_key(): void
    {
        $login = RateLimit::perMinute(1)->withKey('login');
        $signup = RateLimit::perMinute(1)->withKey('signup');

        $this->rateLimit
            ->exhaust($login)
            ->assertThrottled($login)
            ->assertNotThrottled($signup);
    }

    #[Test]
    public function clearing_a_limit_leaves_the_other_keys_alone(): void
    {
        $login = RateLimit::perMinute(1)->withKey('login');
        $signup = RateLimit::perMinute(1)->withKey('signup');

        $this->rateLimit
            ->exhaust($login)
            ->exhaust($signup)
            ->clear($login)
            ->assertNotThrottled($login)
            ->assertThrottled($signup);
    }

    #[Test]
    public function faking_again_discards_every_recorded_attempt(): void
    {
        $limit = RateLimit::perMinute(3)->withKey('login');

        $this->rateLimit->exhaust($limit)->assertThrottled($limit);

        $this->rateLimit->fake()->assertNotThrottled($limit)->assertHits($limit, 0);
    }

    #[Test]
    public function a_window_closes_once_the_clock_moves_past_it(): void
    {
        $limit = RateLimit::perMinute(1)->withKey('login');

        $this->rateLimit->exhaust($limit)->assertThrottled($limit);

        $this->clock->plus(Duration::minutes(2));
        $this->rateLimit->assertNotThrottled($limit);
    }
}
