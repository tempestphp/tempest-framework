<?php

declare(strict_types=1);

namespace Tempest\HttpClient;

use Psr\Http\Client\ClientInterface;
use PsrDiscovery\Discover;
use Tempest\Container\Container;
use Tempest\Container\Initializer;
use Tempest\Container\Singleton;
use Tempest\HttpClient\Driver\Psr18Driver;

final class HttpClientDriverInitializer implements Initializer
{
    #[Singleton]
    public function initialize(Container $container): HttpClientDriver|ClientInterface
    {
        return new Psr18Driver(
            client: Discover::httpClient(),
            uriFactory: Discover::httpUriFactory(),
            requestFactory: Discover::httpRequestFactory(),
            streamFactory: Discover::httpStreamFactory(),
        );
    }
}
