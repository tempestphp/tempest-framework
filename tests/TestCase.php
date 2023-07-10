<?php

namespace Tests\Tempest;

use Tempest\Container\Container;
use Tempest\Interfaces\Server;
use Tempest\Kernel;
use Tempest\Route\Method;

abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    protected Container $container;

    protected Kernel $kernel;

    protected function setUp(): void
    {
        parent::setUp();

        $this->kernel = new Kernel();

        $this->container = $this->kernel->init(__DIR__ . '/../app');

        $this->container->singleton(Server::class, fn() => new TestServer());
    }

    protected function server(
        Method $method = Method::GET,
        string $uri = '/',
        array $body = [],
    ): Server {
        $server = new TestServer(
            method: $method,
            uri: $uri,
            body: $body,
        );

        $this->container->singleton(Server::class, fn() => $server);

        return $server;
    }
}