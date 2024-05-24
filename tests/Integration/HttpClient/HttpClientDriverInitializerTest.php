<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\HttpClient;

use Tempest\HttpClient\Driver\Psr18Driver;
use Tempest\HttpClient\HttpClientDriver;
use Tests\Tempest\Integration\FrameworkIntegrationTest;

/**
 * @internal
 * @small
 */
class HttpClientDriverInitializerTest extends FrameworkIntegrationTest
{
    public function test_container_can_initialize_http_client_driver()
    {
        $driver = $this->container->get(HttpClientDriver::class);

        $this->assertInstanceOf(HttpClientDriver::class, $driver);
        $this->assertInstanceOf(Psr18Driver::class, $driver);
    }
}
