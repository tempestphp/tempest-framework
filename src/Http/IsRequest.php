<?php

declare(strict_types=1);

namespace Tempest\Http;

trait IsRequest
{
    public string $path;
    public array $query;

    public function __construct(
        public Method $method,
        public string $uri,
        public array $body,
        public array $headers = [],
        public array $cookies = [],
    ) {
        $this->path ??= $this->resolvePath();
        $this->query ??= $this->resolveQuery();
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

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function getCookies(): array
    {
        return $this->cookies;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getQuery(): array
    {
        return $this->query;
    }

    private function resolvePath(): string
    {
        $decodedUri = rawurldecode($this->uri);
        $parsedUrl = parse_url($decodedUri);

        return $parsedUrl['path'] ?? '';
    }

    private function resolveQuery(): array
    {
        $decodedUri = rawurldecode($this->uri);
        $parsedUrl = parse_url($decodedUri);
        $queryString = $parsedUrl['query'] ?? '';

        parse_str($queryString, $query);

        return $query;
    }
}
