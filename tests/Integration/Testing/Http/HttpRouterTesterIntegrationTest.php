<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Testing\Http;

use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\Attributes\CoversNothing;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 */
#[CoversNothing]
final class HttpRouterTesterIntegrationTest extends FrameworkIntegrationTestCase
{
    public function test_get_requests(): void
    {
        $this->http
            ->get('/test')
            ->assertOk();
    }

    public function test_get_requests_failure(): void
    {
        $this->expectException(AssertionFailedError::class);

        $this->http
            ->get('/this-route-does-not-exist')
            ->assertOk();
    }

    public function test_head_requests(): void
    {
        $this->http
            ->head('/test')
            ->assertOk();
    }

    public function test_head_requests_failure(): void
    {
        $this->expectException(AssertionFailedError::class);

        $this->http
            ->head('/this-route-does-not-exist')
            ->assertOk();
    }

    public function test_post_requests(): void
    {
        $this->http
            ->post('/test')
            ->assertOk();
    }

    public function test_post_requests_failure(): void
    {
        $this->expectException(AssertionFailedError::class);

        $this->http
            ->post('/this-route-does-not-exist')
            ->assertOk();
    }

    public function test_put_requests(): void
    {
        $this->http
            ->put('/test')
            ->assertOk();
    }

    public function test_put_requests_failure(): void
    {
        $this->expectException(AssertionFailedError::class);

        $this->http
            ->put('/this-route-does-not-exist')
            ->assertOk();
    }

    public function test_delete_requests(): void
    {
        $this->http
            ->delete('/test')
            ->assertOk();
    }

    public function test_delete_requests_failure(): void
    {
        $this->expectException(AssertionFailedError::class);

        $this->http
            ->delete('/this-route-does-not-exist')
            ->assertOk();
    }

    public function test_connect_requests(): void
    {
        $this->http
            ->connect('/test')
            ->assertOk();
    }

    public function test_connect_requests_failure(): void
    {
        $this->expectException(AssertionFailedError::class);

        $this->http
            ->connect('/this-route-does-not-exist')
            ->assertOk();
    }

    public function test_options_requests(): void
    {
        $this->http
            ->options('/test')
            ->assertOk();
    }

    public function test_options_requests_failure(): void
    {
        $this->expectException(AssertionFailedError::class);

        $this->http
            ->options('/this-route-does-not-exist')
            ->assertOk();
    }

    public function test_trace_requests(): void
    {
        $this->http
            ->throwExceptions()
            ->trace('/test')
            ->assertOk();
    }

    public function test_trace_requests_failure(): void
    {
        $this->expectException(AssertionFailedError::class);

        $this->http
            ->trace('/this-route-does-not-exist')
            ->assertOk();
    }

    public function test_patch_requests(): void
    {
        $this->http
            ->patch('/test')
            ->assertOk();
    }

    public function test_patch_requests_failure(): void
    {
        $this->expectException(AssertionFailedError::class);

        $this->http
            ->patch('/this-route-does-not-exist')
            ->assertOk();
    }
}
