<?php

namespace Tempest\Route;

trait BaseRequest
{
    private function __construct(
        public Method $method,
        public string $uri,
        public array $body,
        public string $path,
        public ?string $query = null,
    ) {
    }

    public static function new(
        Method $method,
        string $uri,
        array $body = []
    ): self {
        $decodedUri = rawurldecode($uri);

        $parsedUrl = parse_url($decodedUri);

        return new self(
            method: $method,
            uri: $decodedUri,
            body: $body,
            path: $parsedUrl['path'],
            query: $parsedUrl['query'] ?? null,
        );
    }

    public static function get(string $uri): self
    {
        return self::new(Method::GET, $uri);
    }

    public static function post(string $uri, array $body = []): self
    {
        return self::new(Method::POST, $uri, $body);
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