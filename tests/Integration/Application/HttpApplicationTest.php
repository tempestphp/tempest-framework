<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Application;

use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 */
final class HttpApplicationTest extends FrameworkIntegrationTestCase
{
    public function test_http_application_run(): void
    {
        $this->console->call('ds')->printFormatted();
        $this->http
            ->get('/')
            ->assertOk();
    }
}
