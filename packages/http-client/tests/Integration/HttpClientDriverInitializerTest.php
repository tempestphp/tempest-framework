<?php

declare(strict_types=1);

namespace Tempest\HttpClient\Tests\Integration;

use Tempest\Drift\FrameworkIntegrationTestCase;
use Tempest\HttpClient\Driver\Psr18Driver;
use Tempest\HttpClient\HttpClientDriver;

/**
 * @internal
 */
final class HttpClientDriverInitializerTest extends FrameworkIntegrationTestCase
{
    public function test_container_can_initialize_http_client_driver(): void
    {
        $driver = $this->container->get(HttpClientDriver::class);

        $this->assertInstanceOf(HttpClientDriver::class, $driver);
        $this->assertInstanceOf(Psr18Driver::class, $driver);
    }
}
