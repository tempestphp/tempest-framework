<?php

namespace Tempest\Http;

use Tempest\Interfaces\Container;
use Tempest\Interfaces\Initializer;
use Tempest\Interfaces\Server as ServerInterface;

final readonly class ServerInitializer implements Initializer
{
    public function initialize(string $className, Container $container): GenericServer
    {
        $server = new GenericServer(
            method: Method::from($_SERVER['REQUEST_METHOD']),
            uri: $_SERVER['REQUEST_URI'],
            body: [],
        );

        $container->singleton(ServerInterface::class, fn () => $server);

        return $server;
    }
}
