<?php

declare(strict_types=1);

namespace Tempest\RateLimit\Tests;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Tempest\Cache\GenericCache;
use Tempest\Clock\MockClock;
use Tempest\DateTime\Duration;
use Tempest\RateLimit\Config\CacheRateLimitConfig;
use Tempest\RateLimit\GenericRateLimiter;
use Tempest\RateLimit\Per;
use Tempest\RateLimit\RateLimit;
use Tempest\RateLimit\RateLimiter;
use Tempest\RateLimit\RateLimitKeyWasMissing;
use Tempest\RateLimit\RateLimitWasExceeded;
use Tempest\RateLimit\Storage\CacheRateLimitStorage;

/**
 * @internal
 */
final class RateLimiterTest extends TestCase
{
    private MockClock $clock;

    private RateLimiter $limiter;

    protected function setUp(): void
    {
        parent::setUp();

        $this->clock = new MockClock('2026-01-01 00:00:00');

        $this->limiter = new GenericRateLimiter(
            storage: new CacheRateLimitStorage(
                cache: new GenericCache(new ArrayAdapter(clock: $this->clock->toPsrClock())),
                clock: $this->clock,
                config: new CacheRateLimitConfig(),
            ),
            clock: $this->clock,
        );
    }

    #[Test]
    public function allows_attempts_up_to_the_limit(): void
    {
        $limit = RateLimit::perMinute(3)->withKey('user:1');

        $this->assertTrue($this->limiter->attempt($limit)->allowed);
        $this->assertTrue($this->limiter->attempt($limit)->allowed);

        $third = $this->limiter->attempt($limit);

        $this->assertTrue($third->allowed);
        $this->assertSame(0, $third->remaining);

        $this->assertTrue($this->limiter->attempt($limit)->exceeded);
    }

    #[Test]
    public function counts_down_the_remaining_attempts(): void
    {
        $limit = RateLimit::perMinute(3)->withKey('user:1');

        $this->assertSame(3, $this->limiter->peek($limit)->remaining);
        $this->assertSame(2, $this->limiter->attempt($limit)->remaining);
        $this->assertSame(1, $this->limiter->attempt($limit)->remaining);
        $this->assertSame(1, $this->limiter->peek($limit)->remaining);
    }

    #[Test]
    public function peeking_does_not_consume_an_attempt(): void
    {
        $limit = RateLimit::perMinute(1)->withKey('user:1');

        $this->assertFalse($this->limiter->peek($limit)->exceeded);
        $this->assertFalse($this->limiter->peek($limit)->exceeded);

        $this->limiter->attempt($limit);

        $this->assertTrue($this->limiter->peek($limit)->exceeded);
    }

    #[Test]
    public function keys_do_not_share_a_counter(): void
    {
        $limit = RateLimit::perMinute(1);

        $this->assertTrue($this->limiter->attempt($limit->withKey('user:1'))->allowed);
        $this->assertTrue($this->limiter->attempt($limit->withKey('user:2'))->allowed);
        $this->assertTrue($this->limiter->attempt($limit->withKey('user:1'))->exceeded);
    }

    #[Test]
    public function the_window_reopens_once_it_has_elapsed(): void
    {
        $limit = RateLimit::perMinute(1)->withKey('user:1');

        $this->assertTrue($this->limiter->attempt($limit)->allowed);
        $this->assertTrue($this->limiter->attempt($limit)->exceeded);

        $this->clock->sleep(Duration::seconds(61));

        $this->assertTrue($this->limiter->attempt($limit)->allowed);
    }

    #[Test]
    public function exceeding_the_limit_does_not_extend_the_window(): void
    {
        $limit = RateLimit::perMinute(1)->withKey('user:1');

        $this->limiter->attempt($limit);
        $resetsAt = $this->limiter->peek($limit)->resetsAtInSeconds;

        $this->clock->sleep(Duration::seconds(30));
        $this->limiter->attempt($limit);

        $this->assertSame($resetsAt, $this->limiter->peek($limit)->resetsAtInSeconds);
    }

    #[Test]
    public function reports_how_long_to_wait(): void
    {
        $limit = RateLimit::perMinute(1)->withKey('user:1');

        $this->limiter->attempt($limit);
        $this->clock->sleep(Duration::seconds(20));

        $this->assertSame(40, $this->limiter->attempt($limit)->retryAfterInSeconds);
    }

    #[Test]
    public function clearing_discards_the_recorded_attempts(): void
    {
        $limit = RateLimit::perMinute(1)->withKey('user:1');

        $this->limiter->attempt($limit);
        $this->assertTrue($this->limiter->peek($limit)->exceeded);

        $this->limiter->clear($limit);

        $this->assertFalse($this->limiter->peek($limit)->exceeded);
    }

    #[Test]
    public function throttling_executes_the_callback_until_the_limit_is_reached(): void
    {
        $limit = RateLimit::perMinute(1)->withKey('user:1');

        $this->assertSame('executed', $this->limiter->throttle($limit, fn () => 'executed'));

        $this->expectException(RateLimitWasExceeded::class);

        $this->limiter->throttle($limit, fn () => 'executed');
    }

    #[Test]
    public function attempts_may_be_consumed_in_bulk(): void
    {
        $limit = RateLimit::perMinute(10)->withKey('user:1');

        $this->assertSame(6, $this->limiter->attempt($limit, by: 4)->remaining);
        $this->assertTrue($this->limiter->attempt($limit, by: 7)->exceeded);
    }

    #[Test]
    public function an_allowed_attempt_has_nothing_to_wait_for(): void
    {
        $limit = RateLimit::perMinute(2)->withKey('user:1');

        $this->assertSame(0, $this->limiter->peek($limit)->retryAfterInSeconds);
        $this->assertSame(0, $this->limiter->attempt($limit)->retryAfterInSeconds);
    }

    #[Test]
    public function a_limit_without_a_key_is_rejected(): void
    {
        $this->expectException(RateLimitKeyWasMissing::class);

        $this->limiter->attempt(RateLimit::perMinute(1));
    }

    #[Test]
    public function windows_are_expressed_in_any_unit(): void
    {
        $this->assertSame(1.0, RateLimit::perSecond(1)->window->getTotalSeconds());
        $this->assertSame(300.0, RateLimit::perMinute(1, minutes: 5)->window->getTotalSeconds());
        $this->assertSame(3600.0, RateLimit::perHour(1)->window->getTotalSeconds());
        $this->assertSame(86_400.0, Per::DAY->toDuration()->getTotalSeconds());
    }

    #[Test]
    public function peeking_at_an_untouched_limit_reports_no_open_window(): void
    {
        $result = $this->limiter->peek(RateLimit::perMinute(3)->withKey('user:1'));

        $this->assertTrue($result->allowed);
        $this->assertSame(0, $result->hits);
        $this->assertSame(3, $result->remaining);

        // Nothing has been counted yet. No window may be reported as running.
        $this->assertSame($this->clock->seconds(), $result->resetsAtInSeconds);
        $this->assertSame(0, $result->retryAfterInSeconds);
    }

    #[Test]
    public function scoping_appends_to_the_key_a_limit_already_has(): void
    {
        $limit = RateLimit::perMinute(3)->withKey('login');

        $this->assertSame('login:user:1', $limit->scopedTo('user:1')->key);

        // Scoping a keyless limit has nothing to append to, and names it outright.
        $this->assertSame('user:1', RateLimit::perMinute(3)->scopedTo('user:1')->key);
    }
}
