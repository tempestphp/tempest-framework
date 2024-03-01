<?php

declare(strict_types=1);

namespace Tempest\HttpClient;

use Tempest\Container\InitializedBy;
use Tempest\Http\Response;

#[InitializedBy(HttpClientInitializer::class)]
interface HttpClient
{
    public function get(string $uri, array $headers = []): Response;

    public function head(string $uri, array $headers = []): Response;

    public function trace(string $uri, array $headers = []): Response;

    public function post(string $uri, array $headers = [], ?string $body = null): Response;

    public function put(string $uri, array $headers = [], ?string $body = null): Response;

    public function patch(string $uri, array $headers = [], ?string $body = null): Response;

    public function delete(string $uri, array $headers = [], ?string $body = null): Response;

    public function options(string $uri, array $headers = [], ?string $body = null): Response;
}
