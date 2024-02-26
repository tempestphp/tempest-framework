<?php

declare(strict_types=1);

namespace Tempest\Http;

use Tempest\Container\CanInitialize;
use Tempest\Container\Container;
use Tempest\Container\Initializer;

final readonly class ServerInitializer implements Initializer, CanInitialize
{
    public function canInitialize(string $className): bool
    {
        return $className === Server::class;
    }

    public function initialize(Container $container): GenericServer
    {
        // TODO: Because of how we are setting this request method,
        // stuff fails in a major way for testing where we are just
        // passing a request to the router. Ideally, the request is
        // built up before passing to the kernel.
        //
        // Also, where are the headers?!
        $server = new GenericServer(
            method: Method::tryFrom($_SERVER['REQUEST_METHOD']) ?? Method::GET,
            uri: $_SERVER['REQUEST_URI'] ?? '/',
            body: [], // WHY BRENT, WHY?!!!!!!!!!!!!!!!!!!
        );

        $container->singleton(Server::class, fn () => $server);

        return $server;
    }
}
