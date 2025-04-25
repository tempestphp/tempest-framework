<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Application;

use PHPUnit\Framework\Attributes\CoversNothing;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 */
#[CoversNothing]
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
