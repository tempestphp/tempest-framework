<?php

declare(strict_types=1);

namespace Tempest\Http;

final readonly class GenericServer implements Server
{
    public function __construct(
        private Method $method,
        private string $uri,
        private array $body,
    ) {
    }

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
