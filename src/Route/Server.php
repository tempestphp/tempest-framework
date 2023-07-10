<?php

namespace Tempest\Route;

use Tempest\Container\InitializedBy;

#[InitializedBy(ServerInitializer::class)]
final readonly class Server implements \Tempest\Interfaces\Server
{
    public function __construct(
        private Method $method,
        private string $uri,
        private array $body,
    ) {}

    public function getMethod(): Method
    {
        return $this->method;
    }

    public function getUri(): string
    {
        return $this->uri;
    }

    public function getBody(): array
    {
        return $this->body;
    }
}