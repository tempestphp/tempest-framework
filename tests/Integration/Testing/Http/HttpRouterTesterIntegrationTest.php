<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Testing\Http;

use PHPUnit\Framework\AssertionFailedError;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 * @small
 */
final class HttpRouterTesterIntegrationTest extends FrameworkIntegrationTestCase
{
    public function test_get_requests(): void
    {
        $this
            ->http
            ->get('/test')
            ->assertOk();
    }

    public function test_get_requests_failure(): void
    {
        $this->expectException(AssertionFailedError::class);

        $this
            ->http
            ->get('/this-route-does-not-exist')
            ->assertOk();
    }
}
