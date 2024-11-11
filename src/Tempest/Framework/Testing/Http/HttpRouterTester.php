<?php

declare(strict_types=1);

namespace Tempest\Framework\Testing\Http;

use Laminas\Diactoros\ServerRequestFactory;
use Laminas\Diactoros\Stream;
use Psr\Http\Message\ServerRequestInterface as PsrRequest;
use Tempest\Container\Container;
use Tempest\Http\GenericRequest;
use Tempest\Http\Mappers\RequestToPsrRequestMapper;
use Tempest\Http\Method;
use Tempest\Http\Request;
use Tempest\Http\Router;
use function Tempest\map;

final class HttpRouterTester
{
    private array $createdHeaders = [];

    public function __construct(private Container $container)
    {
    }

    public function get(string $uri, array $headers = []): TestResponseHelper
    {
        return $this->sendRequest(
            new GenericRequest(
                method: Method::GET,
                uri: $uri,
                body: [],
                headers: $headers,
            ),
        );
    }

    public function post(string $uri, array $body = [], array $headers = []): TestResponseHelper
    {
        return $this->sendRequest(
            new GenericRequest(
                method: Method::POST,
                uri: $uri,
                body: $body,
                headers: $headers,
            ),
        );
    }

    public function sendRequest(Request $request): TestResponseHelper
    {
        /** @var Router $router */
        $router = $this->container->get(Router::class);

        return new TestResponseHelper(
            $router->dispatch(map($request)->with(RequestToPsrRequestMapper::class)),
        );
    }

    public function makePsrRequest(
        string $uri,
        Method $method = Method::GET,
        array $body = [],
        array $headers = [],
        array $cookies = [],
        array $files = [],
        string $rawBody = null,
    ): PsrRequest {
        $_SERVER['REQUEST_URI'] = $uri;
        $_SERVER['REQUEST_METHOD'] = $method->value;

        foreach ($headers as $key => $value) {
            $this->setHeader($key, $value);
        }

        $_COOKIE = $cookies;
        $_POST = $body;

        $request = ServerRequestFactory::fromGlobals()->withUploadedFiles($files);

        if ($rawBody !== null) {
            $stream = new Stream('php://temp', 'rw');
            $stream->write($rawBody);
            $stream->rewind();

            $request = $request->withBody($stream);
        }

        return $request;
    }

    public function reset(): void
    {
        foreach ($this->createdHeaders as $key => $value) {
            unset($_SERVER[$key]);
        }

        $this->createdHeaders = [];
    }

    private function setHeader(string $key, string $value): void
    {
        $key = strtoupper($key);
        $headerKey = "HTTP_{$key}";

        $_SERVER[$headerKey] = $value;
        $this->createdHeaders[$headerKey] = $value;
    }
}
