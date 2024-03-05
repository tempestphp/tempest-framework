<?php

declare(strict_types=1);

namespace Tempest\HttpClient;

use Http\Discovery\Psr17FactoryDiscovery;
use Http\Discovery\Psr18ClientDiscovery;
use Psr\Http\Client\ClientInterface;
use Tempest\Container\Container;
use Tempest\Container\Initializer;
use Tempest\Container\Singleton;
use Tempest\HttpClient\Driver\Psr18Driver;

#[Singleton]
final class HttpClientDriverInitializer implements Initializer
{
    public function initialize(Container $container): HttpClientDriver|ClientInterface
    {
        return new Psr18Driver(
            client: Psr18ClientDiscovery::find(),
            uriFactory: Psr17FactoryDiscovery::findUriFactory(),
            requestFactory: Psr17FactoryDiscovery::findRequestFactory(),
            streamFactory: Psr17FactoryDiscovery::findStreamFactory()
        );
    }
}
