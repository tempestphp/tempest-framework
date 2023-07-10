<?php

namespace Tempest\Route;

use Tempest\Container\InitializedBy;

#[InitializedBy(RequestInitializer::class)]
final readonly class Request
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
}