<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\HttpClient;

use Tempest\HttpClient\Driver\Psr18Driver;
use Tempest\HttpClient\HttpClientDriver;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 * @small
 */
class HttpClientDriverInitializerTestCase extends FrameworkIntegrationTestCase
{
    public function test_container_can_initialize_http_client_driver()
    {
        $driver = $this->container->get(HttpClientDriver::class);

        $this->assertInstanceOf(HttpClientDriver::class, $driver);
        $this->assertInstanceOf(Psr18Driver::class, $driver);
    }
}
