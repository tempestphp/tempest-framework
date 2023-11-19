<?php

declare(strict_types=1);

namespace Tests\Tempest;

use Tempest\Http\Method;
use Tempest\Interface\CanInitialize;
use Tempest\Interface\Container;
use Tempest\Interface\Initializer;
use Tempest\Interface\Server;

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

    public function initialize(string $className, Container $container): object
    {
        return new TestServer(
            method: $this->method,
            uri: $this->uri,
            body: $this->body,
        );
    }
}
