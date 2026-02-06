<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Route;

use PHPUnit\Framework\Attributes\Test;
use Tempest\Cache\RateLimiting\RateLimiter;
use Tempest\Container\GenericContainer;
use Tempest\Http\Status;
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
    private ?string $previousRemoteAddr = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->previousRemoteAddr = $_SERVER['REMOTE_ADDR'] ?? null;
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';

        // Use a testing rate limiter that is isolated per test
        $this->rateLimiter = new TestingRateLimiter();

        // Unregister any existing singleton and initializer, then add our singleton
        if ($this->container instanceof GenericContainer) {
            $this->container->unregister(RateLimiter::class);
            $this->container->removeInitializer(RateLimiterInitializer::class);
        }
        $this->container->singleton(RateLimiter::class, fn () => $this->rateLimiter);
    }

    protected function tearDown(): void
    {
        if ($this->previousRemoteAddr === null) {
            unset($_SERVER['REMOTE_ADDR']);
        } else {
            $_SERVER['REMOTE_ADDR'] = $this->previousRemoteAddr;
        }

        parent::tearDown();
    }

    #[Test]
    public function allows_requests_within_limit(): void
    {
        $this->http->registerRoute([RateLimitedController::class, 'limited']);

        // First 3 requests should succeed
        for ($i = 0; $i < 3; $i++) {
            $response = $this->http->get('/rate-limited');
            $this->assertSame(Status::OK, $response->status);
            $this->assertSame('success', $response->body);
        }
    }

    #[Test]
    public function blocks_requests_exceeding_limit(): void
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

    #[Test]
    public function includes_rate_limit_headers(): void
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

    #[Test]
    public function includes_retry_after_header_when_limited(): void
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

    #[Test]
    public function routes_without_rate_limit_are_not_affected(): void
    {
        $this->http->registerRoute([RateLimitedController::class, 'noLimit']);

        // Should be able to make unlimited requests
        for ($i = 0; $i < 100; $i++) {
            $response = $this->http->get('/no-rate-limit');
            $this->assertSame(Status::OK, $response->status);
        }
    }

    #[Test]
    public function different_routes_have_separate_limits(): void
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

    #[Test]
    public function custom_key_is_used(): void
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

    #[Test]
    public function remaining_count_decrements(): void
    {
        $this->http->registerRoute([RateLimitedController::class, 'limited']);

        $response1 = $this->http->get('/rate-limited');
        $response1->assertHeaderContains('X-RateLimit-Remaining', '2');

        $response2 = $this->http->get('/rate-limited');
        $response2->assertHeaderContains('X-RateLimit-Remaining', '1');

        $response3 = $this->http->get('/rate-limited');
        $response3->assertHeaderContains('X-RateLimit-Remaining', '0');
    }

    #[Test]
    public function unauthenticated_user_limits_are_scoped_per_client_ip(): void
    {
        $this->http->registerRoute([RateLimitedController::class, 'limitedByUser']);

        $firstClientHeaders = ['X-Forwarded-For' => '203.0.113.10'];
        $secondClientHeaders = ['X-Forwarded-For' => '198.51.100.15'];

        for ($i = 0; $i < 5; $i++) {
            $response = $this->http->get('/rate-limited-by-user', headers: $firstClientHeaders);
            $this->assertSame(Status::OK, $response->status);
        }

        $limitedResponse = $this->http->get('/rate-limited-by-user', headers: $firstClientHeaders);
        $this->assertSame(Status::TOO_MANY_REQUESTS, $limitedResponse->status);

        $differentIpResponse = $this->http->get('/rate-limited-by-user', headers: $secondClientHeaders);
        $this->assertSame(Status::OK, $differentIpResponse->status);
    }
}
