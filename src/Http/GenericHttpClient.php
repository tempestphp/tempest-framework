<?php

declare(strict_types=1);

namespace Tempest\Http;

use Http\Discovery\Psr17FactoryDiscovery;
use Http\Discovery\Psr18ClientDiscovery;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;

final class GenericHttpClient implements HttpClient
{
    private readonly ClientInterface $client;
    private readonly UriFactoryInterface $uriFactory;
    private readonly RequestFactoryInterface $requestFactory;
    private readonly StreamFactoryInterface $streamFactory;

    public function __construct(
        ?ClientInterface $client = null,
        ?UriFactoryInterface $uriFactory = null,
        ?RequestFactoryInterface $requestFactory = null,
        ?StreamFactoryInterface $streamFactory = null
    ) {
        $this->client = $client ?? Psr18ClientDiscovery::find();
        $this->uriFactory = $uriFactory ?? Psr17FactoryDiscovery::findUriFactory();
        $this->requestFactory = $requestFactory ?? Psr17FactoryDiscovery::findRequestFactory();
        $this->streamFactory = $streamFactory ?? Psr17FactoryDiscovery::findStreamFactory();
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

    public function sendRequest(Request|RequestInterface $request): ResponseInterface
    {
        if ($request instanceof Request) {
            $request = $this->createPsr17RequestFromTempest($request);
        }

        return $this->client->sendRequest($request);
    }

    /**
     * @throws ClientExceptionInterface
     */
    private function send(Method $method, string $uri, array $headers = [], ?string $body = null): ResponseInterface
    {
        $uri = $this->uriFactory->createUri($uri);
        $request = $this->requestFactory->createRequest($method->value, $uri);

        foreach ($headers as $name => $value) {
            $request = $request->withHeader($name, $value);
        }

        if ($body !== null) {
            $stream = is_file($body)
                ? $this->streamFactory->createStreamFromFile($body)
                : $this->streamFactory->createStream($body);

            $request = $request->withBody($stream);
        }

        return $this->sendRequest($request);
    }

    private function createPsr17RequestFromTempest(Request $tempestRequest): RequestInterface
    {
        $request = $this->requestFactory->createRequest(
            method: $tempestRequest->getMethod()->value,
            uri: $this->uriFactory->createUri($tempestRequest->getUri())
        );

        foreach ($tempestRequest->getHeaders() as $header => $value) {
            $request = $request->withHeader($header, $value);
        }

        return $request;
    }
}
