<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\HttpClient;

use PHPUnit\Framework\Attributes\Test;
use Tempest\HttpClient\Driver\Psr18Driver;
use Tempest\HttpClient\HttpClientDriver;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 */
final class HttpClientDriverInitializerTest extends FrameworkIntegrationTestCase
{
    #[Test]
    public function container_can_initialize_http_client_driver(): void
    {
        $driver = $this->container->get(HttpClientDriver::class);

        $this->assertInstanceOf(HttpClientDriver::class, $driver);
        $this->assertInstanceOf(Psr18Driver::class, $driver);
    }
}
