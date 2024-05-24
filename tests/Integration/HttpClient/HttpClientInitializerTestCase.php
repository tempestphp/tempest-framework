<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\HttpClient;

use Tempest\HttpClient\GenericHttpClient;
use Tempest\HttpClient\HttpClient;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 * @small
 */
class HttpClientInitializerTestCase extends FrameworkIntegrationTestCase
{
    public function test_container_can_initialize_http_client()
    {
        $httpClient = $this->container->get(HttpClient::class);

        $this->assertInstanceOf(HttpClient::class, $httpClient);
        $this->assertInstanceOf(GenericHttpClient::class, $httpClient);
    }
}
