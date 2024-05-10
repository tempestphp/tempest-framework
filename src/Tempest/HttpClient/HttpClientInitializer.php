<?php

declare(strict_types=1);

namespace Tempest\HttpClient;

use Tempest\Container\Container;
use Tempest\Container\Initializer;

final class HttpClientInitializer implements Initializer
{
    public function initialize(Container $container): HttpClient|GenericHttpClient
    {
        return new GenericHttpClient(
            driver: $container->get(HttpClientDriver::class)
        );
    }
}
