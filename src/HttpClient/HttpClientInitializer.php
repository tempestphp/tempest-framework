<?php

declare(strict_types=1);

namespace Tempest\HttpClient;

use Http\Discovery\Psr17FactoryDiscovery;
use Http\Discovery\Psr18ClientDiscovery;
use Psr\Http\Client\ClientInterface;
use Tempest\Container\CanInitialize;
use Tempest\Container\Container;
use Tempest\Container\Initializer;
use Tempest\Container\RequiresClassName;
use Tempest\HttpClient\Driver\Psr18Driver;

final class HttpClientInitializer implements Initializer, CanInitialize, RequiresClassName
{
    private string $className;

    public function canInitialize(string $className): bool
    {
        return (
            is_a($className, HttpClient::class) ||
            is_a($className, HttpClientDriver::class) ||
            is_a($className, ClientInterface::class)
        );
    }

    public function setClassName(string $className): void
    {
        $this->className = $className;
    }

    public function initialize(Container $container): object
    {
        return $this->className === HttpClient::class
            ? $container->get(GenericHttpClient::class)
            : $this->initializeHttpClientDriver();
    }

    private function initializeHttpClientDriver(): HttpClientDriver
    {
        return new Psr18Driver(
            client: Psr18ClientDiscovery::find(),
            uriFactory: Psr17FactoryDiscovery::findUriFactory(),
            requestFactory: Psr17FactoryDiscovery::findRequestFactory(),
            streamFactory: Psr17FactoryDiscovery::findStreamFactory()
        );
    }
}
