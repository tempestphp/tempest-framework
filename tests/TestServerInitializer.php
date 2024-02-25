<?php

declare(strict_types=1);

namespace Tests\Tempest;

use Tempest\Container\CanInitialize;
use Tempest\Container\Container;
use Tempest\Container\Initializer;
use Tempest\Http\Method;
use Tempest\Http\Server;

final readonly class TestServerInitializer implements Initializer, CanInitialize
{
    public function __construct(
        private Method $method = Method::GET,
        private string $uri = '/',
        private array $body = [],
    ) {
    }

    public function canInitialize(string $className): bool
    {
        return $className === Server::class;
    }

    public function initialize(Container $container): object
    {
        return new TestServer(
            method: $this->method,
            uri: $this->uri,
            body: $this->body,
        );
    }
}
