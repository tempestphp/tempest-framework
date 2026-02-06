<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Route;

use Tempest\Container\GenericContainer;
use Tempest\Http\Status;
use Tempest\Router\RateLimiting\RateLimiter;
use Tempest\Router\RateLimiting\RateLimiterInitializer;
use Tempest\Router\RateLimiting\Testing\TestingRateLimiter;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;
use Tests\Tempest\Integration\Route\Fixtures\RateLimitedController;

/**
 * @internal
 */
final class RateLimitMiddlewareTest extends FrameworkIntegrationTestCase
{
    private TestingRateLimiter $rateLimiter;

    protected function setUp(): void
    {
        parent::setUp();

        // Use a testing rate limiter that is isolated per test
        $this->rateLimiter = new TestingRateLimiter();

        // Unregister any existing singleton and initializer, then add our singleton
        if ($this->container instanceof GenericContainer) {
            $this->container->unregister(RateLimiter::class);
            $this->container->removeInitializer(RateLimiterInitializer::class);
        }
        $this->container->singleton(RateLimiter::class, fn () => $this->rateLimiter);
    }

    public function test_allows_requests_within_limit(): void
    {
        $this->http->registerRoute([RateLimitedController::class, 'limited']);

        // First 3 requests should succeed
        for ($i = 0; $i < 3; $i++) {
            $response = $this->http->get('/rate-limited');
            $this->assertSame(Status::OK, $response->status);
            $this->assertSame('success', $response->body);
        }
    }

    public function test_blocks_requests_exceeding_limit(): void
    {
        $this->http->registerRoute([RateLimitedController::class, 'limited']);

        // Make 3 requests (the limit)
        for ($i = 0; $i < 3; $i++) {
            $this->http->get('/rate-limited');
        }

        // 4th request should be blocked
        $response = $this->http->get('/rate-limited');
        $this->assertSame(Status::TOO_MANY_REQUESTS, $response->status);
    }

    public function test_includes_rate_limit_headers(): void
    {
        $this->http->registerRoute([RateLimitedController::class, 'limited']);

        $response = $this->http->get('/rate-limited');

        $this->assertSame(Status::OK, $response->status);
        $response->assertHasHeader('X-RateLimit-Limit');
        $response->assertHasHeader('X-RateLimit-Remaining');
        $response->assertHasHeader('X-RateLimit-Reset');
        $response->assertHeaderContains('X-RateLimit-Limit', '3');
        $response->assertHeaderContains('X-RateLimit-Remaining', '2');
    }

    public function test_includes_retry_after_header_when_limited(): void
    {
        $this->http->registerRoute([RateLimitedController::class, 'limited']);

        // Exhaust the limit
        for ($i = 0; $i < 3; $i++) {
            $this->http->get('/rate-limited');
        }

        $response = $this->http->get('/rate-limited');

        $this->assertSame(Status::TOO_MANY_REQUESTS, $response->status);
        $response->assertHasHeader('Retry-After');
        $response->assertHasHeader('X-RateLimit-Limit');
        $response->assertHeaderContains('X-RateLimit-Remaining', '0');
    }

    public function test_routes_without_rate_limit_are_not_affected(): void
    {
        $this->http->registerRoute([RateLimitedController::class, 'noLimit']);

        // Should be able to make unlimited requests
        for ($i = 0; $i < 100; $i++) {
            $response = $this->http->get('/no-rate-limit');
            $this->assertSame(Status::OK, $response->status);
        }
    }

    public function test_different_routes_have_separate_limits(): void
    {
        $this->http->registerRoute([RateLimitedController::class, 'limited']);
        $this->http->registerRoute([RateLimitedController::class, 'limitedCustomKey']);

        // Exhaust limit on first route
        for ($i = 0; $i < 3; $i++) {
            $this->http->get('/rate-limited');
        }

        // Second route should still work (different key)
        $response = $this->http->get('/rate-limited-custom-key');
        $this->assertSame(Status::OK, $response->status);
    }

    public function test_custom_key_is_used(): void
    {
        $this->http->registerRoute([RateLimitedController::class, 'limitedCustomKey']);

        // Make 2 requests (the limit for this route)
        $response1 = $this->http->get('/rate-limited-custom-key');
        $response2 = $this->http->get('/rate-limited-custom-key');

        $this->assertSame(Status::OK, $response1->status);
        $this->assertSame(Status::OK, $response2->status);

        // 3rd request should be blocked
        $response3 = $this->http->get('/rate-limited-custom-key');
        $this->assertSame(Status::TOO_MANY_REQUESTS, $response3->status);
    }

    public function test_remaining_count_decrements(): void
    {
        $this->http->registerRoute([RateLimitedController::class, 'limited']);

        $response1 = $this->http->get('/rate-limited');
        $response1->assertHeaderContains('X-RateLimit-Remaining', '2');

        $response2 = $this->http->get('/rate-limited');
        $response2->assertHeaderContains('X-RateLimit-Remaining', '1');

        $response3 = $this->http->get('/rate-limited');
        $response3->assertHeaderContains('X-RateLimit-Remaining', '0');
    }
}
