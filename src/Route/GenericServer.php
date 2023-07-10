<?php

namespace Tempest\Route;

use Tempest\Container\InitializedBy;
use Tempest\Interfaces\Server;

#[InitializedBy(ServerInitializer::class)]
final readonly class GenericServer implements Server
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