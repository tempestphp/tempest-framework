<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Testing\Http;

use PHPUnit\Framework\AssertionFailedError;
use Tempest\Testing\IntegrationTest;

/**
 * @internal
 * @small
 */
class HttpRouterTesterIntegrationTest extends IntegrationTest
{
    public function test_get_requests()
    {
        $this
            ->http
            ->get('/test')
            ->assertOk();
    }

    public function test_get_requests_failure()
    {
        $this->expectException(AssertionFailedError::class);

        $this
            ->http
            ->get('/this-route-does-not-exist')
            ->assertOk();
    }
}
