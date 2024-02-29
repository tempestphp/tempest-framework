<?php

declare(strict_types=1);

namespace Tempest\Http;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;

final class GenericHttpClient implements HttpClient
{
    public function __construct(
        private readonly ClientInterface $client,
        private readonly UriFactoryInterface $uriFactory,
        private readonly RequestFactoryInterface $requestFactory,
        private readonly StreamFactoryInterface $streamFactory
    ) {
    }

    public function get(string $uri, array $headers = []): ResponseInterface
    {
        return $this->send(
            method: Method::GET,
            uri: $uri,
            headers: $headers
        );
    }

    public function head(string $uri, array $headers = []): ResponseInterface
    {
        return $this->send(
            method: Method::HEAD,
            uri: $uri,
            headers: $headers,
        );
    }

    public function post(string $uri, array $headers = [], ?string $body = null): ResponseInterface
    {
        return $this->send(
            method: Method::POST,
            uri: $uri,
            headers: $headers,
            body: $body
        );
    }

    public function trace(string $uri, array $headers = []): ResponseInterface
    {
        return $this->send(
            method: Method::TRACE,
            uri: $uri,
            headers: $headers
        );
    }

    public function put(string $uri, array $headers = [], ?string $body = null): ResponseInterface
    {
        return $this->send(
            method: Method::PUT,
            uri: $uri,
            headers: $headers,
            body: $body
        );
    }

    public function patch(string $uri, array $headers = [], ?string $body = null): ResponseInterface
    {
        return $this->send(
            method: Method::PATCH,
            uri: $uri,
            headers: $headers,
            body: $body
        );
    }

    public function delete(string $uri, array $headers = [], ?string $body = null): ResponseInterface
    {
        return $this->send(
            method: Method::DELETE,
            uri: $uri,
            headers: $headers,
            body: $body
        );
    }

    public function options(string $uri, array $headers = [], ?string $body = null): ResponseInterface
    {
        return $this->send(
            method: Method::OPTIONS,
            uri: $uri,
            headers: $headers,
            body: $body
        );
    }

    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        return $this->client->sendRequest($request);
    }

    private function send(Method $method, string $uri, array $headers = [], ?string $body = null): ResponseInterface
    {
        $uri = $this->uriFactory->createUri($uri);
        $request = $this->requestFactory->createRequest($method->value, $uri);

        foreach ($headers as $name => $value) {
            $request = $request->withHeader($name, $value);
        }

        $stream = is_file($body)
            ? $this->streamFactory->createStreamFromFile($body)
            : $this->streamFactory->createStream($body);

        $request = $request->withBody($stream);

        return $this->sendRequest($request);
    }
}
