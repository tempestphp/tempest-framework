<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Application;

use Tempest\Testing\IntegrationTest;

class HttpApplicationTest extends IntegrationTest
{
    /** @test */
    public function test_http_application_run()
    {
        $this->http
            ->get('/')
            ->assertOk();
    }
}
