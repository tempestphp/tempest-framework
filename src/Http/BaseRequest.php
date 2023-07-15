<?php

declare(strict_types=1);

namespace Tempest\Http;

trait BaseRequest
{
    public string $path;
    public ?string $query = null;

    public function __construct(
        public Method $method,
        public string $uri,
        public array $body,
    ) {
        $decodedUri = rawurldecode($uri);
        $parsedUrl = parse_url($decodedUri);

        $this->path = $parsedUrl['path'];
        $this->query = $parsedUrl['query'] ?? null;
    }

    public function get(): self
    {
        $this->method = Method::GET;

        return $this;
    }

    public function post(?array $body = null): self
    {
        $this->method = Method::POST;
        $this->body = $body ?? $this->body;

        return $this;
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

    public function getPath(): string
    {
        return $this->path;
    }

    public function getQuery(): ?string
    {
        return $this->query;
    }
}
