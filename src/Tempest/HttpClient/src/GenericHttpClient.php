<?php

declare(strict_types=1);

namespace Tempest\HttpClient;

use Tempest\Http\Method;
use Tempest\Router\GenericRequest;
use Tempest\Router\Request;
use Tempest\Router\Response;

final class GenericHttpClient implements HttpClient
{
    public function __construct(
        private HttpClientDriver $driver,
    ) {}

    public function sendRequest(Request $request): Response
    {
        return $this->driver->send($request);
    }

    public function get(string $uri, array $headers = []): Response
    {
        return $this->send(
            method: Method::GET,
            uri: $uri,
            headers: $headers,
        );
    }

    public function head(string $uri, array $headers = []): Response
    {
        return $this->send(
            method: Method::HEAD,
            uri: $uri,
            headers: $headers,
        );
    }

    public function post(string $uri, array $headers = [], ?string $body = null): Response
    {
        return $this->send(
            method: Method::POST,
            uri: $uri,
            headers: $headers,
            body: $body,
        );
    }

    public function trace(string $uri, array $headers = []): Response
    {
        return $this->send(
            method: Method::TRACE,
            uri: $uri,
            headers: $headers,
        );
    }

    public function put(string $uri, array $headers = [], ?string $body = null): Response
    {
        return $this->send(
            method: Method::PUT,
            uri: $uri,
            headers: $headers,
            body: $body,
        );
    }

    public function patch(string $uri, array $headers = [], ?string $body = null): Response
    {
        return $this->send(
            method: Method::PATCH,
            uri: $uri,
            headers: $headers,
            body: $body,
        );
    }

    public function delete(string $uri, array $headers = [], ?string $body = null): Response
    {
        return $this->send(
            method: Method::DELETE,
            uri: $uri,
            headers: $headers,
            body: $body,
        );
    }

    public function options(string $uri, array $headers = [], ?string $body = null): Response
    {
        return $this->send(
            method: Method::OPTIONS,
            uri: $uri,
            headers: $headers,
            body: $body,
        );
    }

    private function send(Method $method, string $uri, array $headers = [], ?string $body = null): Response
    {
        $request = new GenericRequest(
            method: $method,
            uri: $uri,
            // TODO: This bit is dumb, but we need to refactor
            // requests before we can change it.
            body: $body ? json_decode($body, true) : [],
            headers: $headers,
        );

        return $this->driver->send($request);
    }
}
