<?php

declare(strict_types=1);

namespace Tests\Tempest\Testing\Http;

use Exception;
use Tempest\Testing\IntegrationTest;

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
        // TODO: we need a NotFoundException
        $this->expectException(Exception::class);

        $this
            ->http
            ->get('/this-route-does-not-exist')
            ->assertOk();
    }
}
