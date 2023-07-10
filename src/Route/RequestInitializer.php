<?php

namespace Tempest\Route;

use Tempest\Interfaces\Container;
use Tempest\Interfaces\Server as ServerInterface;

final readonly class RequestInitializer
{
    public function __construct(private ServerInterface $server) {}

    public function __invoke(Container $container): Request
    {
        $request = Request::new(
            method: $this->server->getMethod(),
            uri: $this->server->getUri(),
            body: $this->server->getBody(),
        );

        $container->singleton(Request::class, fn () => $request);

        return $request;
    }
}