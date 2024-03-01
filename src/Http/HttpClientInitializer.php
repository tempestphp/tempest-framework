<?php

declare(strict_types=1);

namespace Tempest\Http;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\HttpFactory;
use Tempest\Container\Container;
use Tempest\Container\Initializer;

final class HttpClientInitializer implements Initializer
{
    public function initialize(Container $container): object
    {
        $psr17Factory = new HttpFactory();
        $client = new Client();
        $httpClient = new GenericHttpClient(
            client: $client,
            uriFactory: $psr17Factory,
            requestFactory: $psr17Factory,
            streamFactory: $psr17Factory
        );

        $container->singleton(HttpClient::class, fn () => $httpClient);

        return $httpClient;
    }
}
