<?php

declare(strict_types=1);

namespace Tempest\Http;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\ResponseInterface;
use Tempest\Container\InitializedBy;

#[InitializedBy(HttpClientInitializer::class)]
interface HttpClient extends ClientInterface
{
    public function get(string $uri, array $headers = []): ResponseInterface;

    public function head(string $uri, array $headers = []): ResponseInterface;

    public function trace(string $uri, array $headers = []): ResponseInterface;

    public function post(string $uri, array $headers = [], ?string $body = null): ResponseInterface;

    public function put(string $uri, array $headers = [], ?string $body = null): ResponseInterface;

    public function patch(string $uri, array $headers = [], ?string $body = null): ResponseInterface;

    public function delete(string $uri, array $headers = [], ?string $body = null): ResponseInterface;

    public function options(string $uri, array $headers = [], ?string $body = null): ResponseInterface;
}
