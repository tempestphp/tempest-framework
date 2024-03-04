<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Application;

use Tempest\Testing\IntegrationTest;

/**
 * @internal
 * @small
 */
class HttpApplicationTest extends IntegrationTest
{
    public function test_http_application_run()
    {
        $this->http
            ->get('/')
            ->assertOk();
    }
}
