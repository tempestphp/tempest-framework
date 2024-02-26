<?php

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
    ) {}

    public function get(string $uri, array $headers = []): ResponseInterface
    {
        return $this->send(
            method: Method::GET,
            uri: $uri,
            headers: $headers
        );
    }

    public function post(string $uri, string $content, array $headers = []): ResponseInterface
    {
        return $this->send(
            method: Method::POST,
            uri: $uri,
            content: $content,
            headers: $headers
        );
    }

    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        return $this->client->sendRequest($request);
    }

    private function send(Method $method, string $uri, ?string $content = null, array $headers = []): ResponseInterface
    {
        $uri = $this->uriFactory->createUri($uri);
        $request = $this->requestFactory->createRequest($method->value, $uri);

        foreach ($headers as $name => $value) {
            $request = $request->withHeader($name, $value);
        }

        $stream = is_file($content)
            ? $this->streamFactory->createStreamFromFile($content)
            : $this->streamFactory->createStream($content);

        $request = $request->withBody($stream);

        return $this->sendRequest($request);
    }
}