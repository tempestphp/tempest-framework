<?php

declare(strict_types=1);

namespace Tests\Tempest;

use Tempest\Http\Method;
use Tempest\Http\Server;

final readonly class TestServer implements Server
{
    public function __construct(
        private Method $method = Method::GET,
        private string $uri = '/',
        private array $body = [],
        private array $headers = [],
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

    public function getHeaders(): array
    {
        return $this->headers;
    }
}
