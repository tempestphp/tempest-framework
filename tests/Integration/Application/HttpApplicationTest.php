<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Application;

use Tempest\Drift\FrameworkIntegrationTestCase;

/**
 * @internal
 */
final class HttpApplicationTest extends FrameworkIntegrationTestCase
{
    public function test_http_application_run(): void
    {
        $this->http
            ->throwExceptions()
            ->get('/')
            ->assertOk();
    }
}
