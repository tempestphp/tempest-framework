<?php

declare(strict_types=1);

namespace Tempest\HttpClient\Driver;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;
use Tempest\Http\Status;
use Tempest\HttpClient\HttpClientDriver;
use Tempest\Router\GenericResponse;
use Tempest\Router\Request;
use Tempest\Router\Response;

final class Psr18Driver implements ClientInterface, HttpClientDriver
{
    public function __construct(
        private ClientInterface $client,
        private UriFactoryInterface $uriFactory,
        private RequestFactoryInterface $requestFactory,
        private StreamFactoryInterface $streamFactory,
    ) {
    }

    public function send(Request $request): Response
    {
        $psrRequest = $this->convertTempestRequestToPsrRequest($request);

        return $this->convertPsrResponseToTempestResponse(
            $this->sendRequest($psrRequest),
        );
    }

    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        return $this->client->sendRequest($request);
    }

    private function convertTempestRequestToPsrRequest(Request $tempestRequest): RequestInterface
    {
        $request = $this->requestFactory->createRequest(
            method: $tempestRequest->getMethod()->value,
            uri: $this->uriFactory->createUri(
                $tempestRequest->getUri(),
            ),
        );

        foreach ($tempestRequest->getHeaders() as $name => $value) {
            $request = $request->withHeader($name, $value);
        }

        // TODO: This is crappy and doesn't support stuff we need to.
        // Eventually the array will be a string.
        if ($tempestRequest->getBody() !== []) {
            $body = json_encode($tempestRequest->getBody());
            $request = $request->withBody(
                $this->streamFactory->createStream($body),
            );
        }

        return $request;
    }

    private function convertPsrResponseToTempestResponse(ResponseInterface $response): Response
    {
        return new GenericResponse(
            status: Status::code($response->getStatusCode()),
            body: $response->getBody()->getContents(),
            headers: $response->getHeaders(),
        );
    }
}
