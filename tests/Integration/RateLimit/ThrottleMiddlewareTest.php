<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\RateLimit;

use PHPUnit\Framework\Attributes\Test;
use Tempest\DateTime\Duration;
use Tempest\Http\Status;
use Tempest\RateLimit\Config\CacheRateLimitConfig;
use Tempest\RateLimit\RateLimit;
use Tempest\RateLimit\RateLimiter;
use Tests\Tempest\Fixtures\RateLimit\UnidentifiedKeyResolver;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 */
final class ThrottleMiddlewareTest extends FrameworkIntegrationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Rate limit counters outlive a single request by design, so each test gets its own storage.
        $this->rateLimit->fake();
    }

    #[Test]
    public function requests_are_allowed_up_to_the_declared_limit(): void
    {
        $this->http->fromIp('203.0.113.9')->get('/throttled')->assertOk();
        $this->http->fromIp('203.0.113.9')->get('/throttled')->assertOk();
        $this->http->fromIp('203.0.113.9')->get('/throttled')->assertStatus(Status::TOO_MANY_REQUESTS);
    }

    #[Test]
    public function routes_without_the_attribute_are_not_throttled(): void
    {
        foreach (range(1, 5) as $ignored) {
            $this->http->fromIp('203.0.113.9')->get('/not-throttled')->assertOk();
        }
    }

    #[Test]
    public function counters_are_scoped_per_client(): void
    {
        $this->http->fromIp('203.0.113.9')->get('/throttled')->assertOk();
        $this->http->fromIp('203.0.113.9')->get('/throttled')->assertOk();

        $this->http->fromIp('198.51.100.7')->get('/throttled')->assertOk();
    }

    #[Test]
    public function counters_are_shared_between_spellings_of_the_same_address(): void
    {
        $this->http->fromIp('127.0.0.1')->get('/throttled')->assertOk();
        $this->http->fromIp('::ffff:127.0.0.1')->get('/throttled')->assertOk();

        $this->http->fromIp('127.0.0.1')->get('/throttled')->assertStatus(Status::TOO_MANY_REQUESTS);
    }

    #[Test]
    public function unidentified_clients_share_a_single_counter(): void
    {
        $this->container->config(new CacheRateLimitConfig(keyResolverClass: UnidentifiedKeyResolver::class));

        $this->http->fromIp('203.0.113.9')->get('/throttled')->assertOk();
        $this->http->fromIp('198.51.100.7')->get('/throttled')->assertOk();

        // Neither client could be identified, so the limit is reached despite the differing addresses.
        $this->http->fromIp('192.0.2.1')->get('/throttled')->assertStatus(Status::TOO_MANY_REQUESTS);
    }

    #[Test]
    public function counters_are_scoped_per_route(): void
    {
        $this->http->fromIp('203.0.113.9')->get('/throttled')->assertOk();
        $this->http->fromIp('203.0.113.9')->get('/throttled')->assertOk();

        $this->http->fromIp('203.0.113.9')->get('/throttled-twice')->assertOk();
    }

    #[Test]
    public function responses_carry_the_remaining_allowance(): void
    {
        $this->http
            ->fromIp('203.0.113.9')
            ->get('/throttled')
            ->assertOk()
            ->assertHeaderContains('x-ratelimit-limit', '2')
            ->assertHeaderContains('x-ratelimit-remaining', '1');

        $this->http
            ->fromIp('203.0.113.9')
            ->get('/throttled')
            ->assertHeaderContains('x-ratelimit-remaining', '0');
    }

    #[Test]
    public function throttled_responses_say_when_to_retry(): void
    {
        $this->http->fromIp('203.0.113.9')->get('/throttled');
        $this->http->fromIp('203.0.113.9')->get('/throttled');

        $this->http
            ->fromIp('203.0.113.9')
            ->get('/throttled')
            ->assertStatus(Status::TOO_MANY_REQUESTS)
            ->assertHasHeader('retry-after')
            ->assertHeaderContains('x-ratelimit-remaining', '0');
    }

    #[Test]
    public function headers_may_be_disabled(): void
    {
        $this->container->config(new CacheRateLimitConfig(includeHeaders: false));

        $this->http
            ->fromIp('203.0.113.9')
            ->get('/throttled')
            ->assertOk()
            ->assertDoesNotHaveHeader('x-ratelimit-limit');

        $this->http->fromIp('203.0.113.9')->get('/throttled')->assertOk();

        // `includeHeaders` governs the allowance headers only. A 429 still carries `retry-after`,
        // without which a client has no way of knowing when to come back.
        $this->http
            ->fromIp('203.0.113.9')
            ->get('/throttled')
            ->assertStatus(Status::TOO_MANY_REQUESTS)
            ->assertHasHeader('retry-after')
            ->assertDoesNotHaveHeader('x-ratelimit-limit');
    }

    #[Test]
    public function the_narrowest_of_several_limits_is_reported(): void
    {
        $this->http
            ->fromIp('203.0.113.9')
            ->get('/throttled-twice')
            ->assertOk()
            ->assertHeaderContains('x-ratelimit-limit', '1')
            ->assertHeaderContains('x-ratelimit-remaining', '0');

        $this->http
            ->fromIp('203.0.113.9')
            ->get('/throttled-twice')
            ->assertStatus(Status::TOO_MANY_REQUESTS);
    }

    #[Test]
    public function a_profile_resolves_the_limits_from_the_request(): void
    {
        $this->http->fromIp('203.0.113.9')->get('/throttled-by-profile')->assertOk();
        $this->http->fromIp('203.0.113.9')->get('/throttled-by-profile')->assertStatus(Status::TOO_MANY_REQUESTS);

        $this->http
            ->fromIp('203.0.113.9')
            ->get('/throttled-by-profile', headers: ['X-Api-Key' => 'premium'])
            ->assertOk();
    }

    #[Test]
    public function limits_returned_by_a_profile_get_a_counter_each(): void
    {
        // The profile returns three per minute and one per day. Each gets its own counter, so the
        // first request spends one of each. Sharing a counter would spend it twice, rejecting the
        // first request against the daily limit.
        $this->http->fromIp('203.0.113.9')->get('/throttled-by-tiers')->assertOk();
        $this->http->fromIp('203.0.113.9')->get('/throttled-by-tiers')->assertStatus(Status::TOO_MANY_REQUESTS);
    }

    #[Test]
    public function limits_sharing_a_window_get_a_counter_each(): void
    {
        // Both attributes describe a one minute window, so neither may derive its key from it.
        $this->http->fromIp('203.0.113.9')->get('/throttled-by-two-identical-windows')->assertOk();
        $this->http->fromIp('203.0.113.9')->get('/throttled-by-two-identical-windows')->assertOk();
        $this->http
            ->fromIp('203.0.113.9')
            ->get('/throttled-by-two-identical-windows')
            ->assertStatus(Status::TOO_MANY_REQUESTS);
    }

    #[Test]
    public function a_rejected_request_does_not_burn_the_wider_windows(): void
    {
        $clock = $this->clock('2026-01-01 00:00:00');

        // Storage captures the clock when it's faked, so it has to be faked again against this one.
        $this->rateLimit->fake();

        // The route allows two requests per minute and three per day, in that declaration order.
        $this->http->fromIp('203.0.113.9')->get('/throttled-widest-first')->assertOk();
        $this->http->fromIp('203.0.113.9')->get('/throttled-widest-first')->assertOk();
        $this->http->fromIp('203.0.113.9')->get('/throttled-widest-first')->assertStatus(Status::TOO_MANY_REQUESTS);
        $this->http->fromIp('203.0.113.9')->get('/throttled-widest-first')->assertStatus(Status::TOO_MANY_REQUESTS);

        $clock->sleep(Duration::seconds(61));

        // The rejected requests cost nothing, so one of the three daily attempts is still left.
        $this->http->fromIp('203.0.113.9')->get('/throttled-widest-first')->assertOk();
        $this->http->fromIp('203.0.113.9')->get('/throttled-widest-first')->assertStatus(Status::TOO_MANY_REQUESTS);
    }

    #[Test]
    public function a_limit_declared_on_the_controller_covers_every_route_it_exposes(): void
    {
        $this->http->fromIp('203.0.113.9')->get('/class-throttled/second')->assertOk();
        $this->http->fromIp('203.0.113.9')->get('/class-throttled/second')->assertOk();
        $this->http->fromIp('203.0.113.9')->get('/class-throttled/second')->assertOk();

        // The controller allows three requests per hour in total, so the other route is out of allowance too.
        $this->http->fromIp('203.0.113.9')->get('/class-throttled/first')->assertStatus(Status::TOO_MANY_REQUESTS);
    }

    #[Test]
    public function a_route_may_narrow_the_limit_declared_on_its_controller(): void
    {
        $this->http->fromIp('203.0.113.9')->get('/class-throttled/first')->assertOk();

        // The route allows one request per minute, well within the controller's hourly allowance.
        $this->http->fromIp('203.0.113.9')->get('/class-throttled/first')->assertStatus(Status::TOO_MANY_REQUESTS);
        $this->http->fromIp('203.0.113.9')->get('/class-throttled/second')->assertOk();
    }

    #[Test]
    public function a_rejected_request_does_not_consume_the_limits_behind_the_one_it_hit(): void
    {
        $this->http->fromIp('203.0.113.9')->get('/class-throttled/first')->assertOk();

        // The route's own limit is exhausted, so these never reach the controller's hourly allowance.
        $this->http->fromIp('203.0.113.9')->get('/class-throttled/first')->assertStatus(Status::TOO_MANY_REQUESTS);
        $this->http->fromIp('203.0.113.9')->get('/class-throttled/first')->assertStatus(Status::TOO_MANY_REQUESTS);

        $this->http->fromIp('203.0.113.9')->get('/class-throttled/second')->assertOk();
        $this->http->fromIp('203.0.113.9')->get('/class-throttled/second')->assertOk();
    }

    #[Test]
    public function throttling_may_be_prevented_and_allowed_again(): void
    {
        $this->rateLimit->preventThrottling();

        foreach (range(1, 5) as $ignored) {
            $this->http->fromIp('203.0.113.9')->get('/throttled')->assertOk();
        }

        $this->rateLimit->allowThrottling();

        $this->http->fromIp('203.0.113.9')->get('/throttled')->assertOk();
        $this->http->fromIp('203.0.113.9')->get('/throttled')->assertOk();
        $this->http->fromIp('203.0.113.9')->get('/throttled')->assertStatus(Status::TOO_MANY_REQUESTS);
    }

    #[Test]
    public function limits_describing_the_same_allowance_describe_one_limit(): void
    {
        // Both attributes allow two requests per minute, which is one allowance declared twice. It's
        // spent once per request, so the route behaves as though one had been declared.
        $this->http->fromIp('203.0.113.9')->get('/throttled-by-two-identical-limits')->assertOk();
        $this->http
            ->fromIp('203.0.113.9')
            ->get('/throttled-by-two-identical-limits')
            ->assertOk()
            ->assertHeaderContains('x-ratelimit-remaining', '0');

        $this->http
            ->fromIp('203.0.113.9')
            ->get('/throttled-by-two-identical-limits')
            ->assertStatus(Status::TOO_MANY_REQUESTS);
    }

    #[Test]
    public function routes_naming_the_same_bucket_share_an_allowance(): void
    {
        $this->http->fromIp('203.0.113.9')->get('/throttled-by-shared-bucket/first')->assertOk();
        $this->http->fromIp('203.0.113.9')->get('/throttled-by-shared-bucket/second')->assertOk();

        // The bucket allows two requests in total, whichever of the two routes they are made against.
        $this->http
            ->fromIp('203.0.113.9')
            ->get('/throttled-by-shared-bucket/first')
            ->assertStatus(Status::TOO_MANY_REQUESTS);
    }

    #[Test]
    public function a_shared_bucket_keeps_windows_of_different_spans_apart(): void
    {
        // Exhausts the per-minute allowance of the `shared` bucket.
        $this->http->fromIp('203.0.113.9')->get('/throttled-by-shared-bucket/first')->assertOk();
        $this->http->fromIp('203.0.113.9')->get('/throttled-by-shared-bucket/second')->assertOk();
        $this->http
            ->fromIp('203.0.113.9')
            ->get('/throttled-by-shared-bucket/first')
            ->assertStatus(Status::TOO_MANY_REQUESTS);

        // The hourly limit names the same bucket, but measures another span. Sharing a counter with
        // the limits above would leave the span of the window to whichever request opened it.
        $this->http->fromIp('203.0.113.9')->get('/throttled-by-shared-bucket/hourly')->assertOk();
    }

    #[Test]
    public function a_shared_bucket_is_still_scoped_per_client(): void
    {
        $this->http->fromIp('203.0.113.9')->get('/throttled-by-shared-bucket/first')->assertOk();
        $this->http->fromIp('203.0.113.9')->get('/throttled-by-shared-bucket/second')->assertOk();

        $this->http->fromIp('198.51.100.7')->get('/throttled-by-shared-bucket/first')->assertOk();
    }

    #[Test]
    public function requests_without_an_address_share_a_single_bucket(): void
    {
        $this->http->get('/throttled')->assertOk();
        $this->http->get('/throttled')->assertOk();
        $this->http->get('/throttled')->assertStatus(Status::TOO_MANY_REQUESTS);
    }

    #[Test]
    public function a_route_may_narrow_its_controllers_shared_bucket_allowance(): void
    {
        $this->http->fromIp('203.0.113.9')->get('/class-throttled/shared-bucket')->assertOk();
        $this->http
            ->fromIp('203.0.113.9')
            ->get('/class-throttled/shared-bucket')
            ->assertStatus(Status::TOO_MANY_REQUESTS);
    }

    #[Test]
    public function http_methods_on_the_same_handler_have_independent_allowances(): void
    {
        $this->http->fromIp('203.0.113.9')->get('/throttled-by-http-method')->assertOk();
        $this->http->fromIp('203.0.113.9')->post('/throttled-by-http-method')->assertOk();

        $this->http->fromIp('203.0.113.9')->get('/throttled-by-http-method')->assertStatus(Status::TOO_MANY_REQUESTS);
        $this->http->fromIp('203.0.113.9')->post('/throttled-by-http-method')->assertStatus(Status::TOO_MANY_REQUESTS);
    }

    #[Test]
    public function a_counter_named_by_a_profile_can_be_inspected_through_the_limiter(): void
    {
        $this->http->fromIp('203.0.113.9')->get('/throttled-by-shared-counter/first')->assertOk();

        // A key the application builds is used as written, so it addresses the counter directly.
        $result = $this->container->get(RateLimiter::class)->peek(RateLimit::perMinute(2)->withKey('shared-counter'));

        $this->assertSame(1, $result->hits);
        $this->assertSame(1, $result->remaining);
    }

    #[Test]
    public function a_counter_named_by_a_profile_can_be_cleared_through_the_limiter(): void
    {
        $this->http->fromIp('203.0.113.9')->get('/throttled-by-shared-counter/first')->assertOk();
        $this->http->fromIp('203.0.113.9')->get('/throttled-by-shared-counter/second')->assertOk();
        $this->http->fromIp('203.0.113.9')->get('/throttled-by-shared-counter/first')->assertStatus(Status::TOO_MANY_REQUESTS);

        $this->container->get(RateLimiter::class)->clear(RateLimit::perMinute(2)->withKey('shared-counter'));

        $this->http->fromIp('203.0.113.9')->get('/throttled-by-shared-counter/first')->assertOk();
    }

    #[Test]
    public function a_counter_named_by_a_profile_is_not_scoped_to_the_client(): void
    {
        $this->http->fromIp('203.0.113.9')->get('/throttled-by-shared-counter/first')->assertOk();
        $this->http->fromIp('203.0.113.10')->get('/throttled-by-shared-counter/first')->assertOk();

        // The profile's key carries no client, so it counts every client into one allowance.
        $this->http->fromIp('203.0.113.11')->get('/throttled-by-shared-counter/first')->assertStatus(Status::TOO_MANY_REQUESTS);
    }

    #[Test]
    public function a_named_bucket_is_scoped_to_the_client(): void
    {
        $this->http->fromIp('203.0.113.9')->get('/throttled-by-shared-bucket/first')->assertOk();
        $this->http->fromIp('203.0.113.9')->get('/throttled-by-shared-bucket/second')->assertOk();
        $this->http->fromIp('203.0.113.9')->get('/throttled-by-shared-bucket/first')->assertStatus(Status::TOO_MANY_REQUESTS);

        // A bucket is a constant, so it groups routes per client rather than across all of them.
        $this->http->fromIp('203.0.113.10')->get('/throttled-by-shared-bucket/first')->assertOk();
    }
}
