<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Application;

use PHPUnit\Framework\Attributes\Test;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 */
final class HttpApplicationTest extends FrameworkIntegrationTestCase
{
    #[Test]
    public function http_application_run(): void
    {
        $this->http
            ->get('/')
            ->assertOk();
    }
}
