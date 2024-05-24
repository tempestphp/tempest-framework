<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Application;

use Tests\Tempest\Integration\FrameworkIntegrationTest;

/**
 * @internal
 * @small
 */
class HttpApplicationTest extends FrameworkIntegrationTest
{
    public function test_http_application_run()
    {
        $this->http
            ->get('/')
            ->assertOk();
    }
}
