<?php

declare(strict_types=1);

namespace Tempest\Http;

use Tempest\Interfaces\CanInitialize;
use Tempest\Interfaces\Container;
use Tempest\Interfaces\Initializer;
use Tempest\Interfaces\Server;

final readonly class ServerInitializer implements Initializer, CanInitialize
{
    public function canInitialize(string $className): bool
    {
        return $className === Server::class;
    }

    public function initialize(string $className, Container $container): GenericServer
    {
        $server = new GenericServer(
            method: Method::tryFrom($_SERVER['REQUEST_METHOD']) ?? Method::GET,
            uri: $_SERVER['REQUEST_URI'] ?? '/',
            body: [],
        );

        $container->singleton(Server::class, fn () => $server);

        return $server;
    }
}
