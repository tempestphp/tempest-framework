<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Application;

use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 * @small
 */
class HttpApplicationTestCase extends FrameworkIntegrationTestCase
{
    public function test_http_application_run()
    {
        $this->http
            ->get('/')
            ->assertOk();
    }
}
