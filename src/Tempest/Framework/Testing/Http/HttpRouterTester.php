<?php

declare(strict_types=1);

namespace Tempest\Framework\Testing\Http;

use Laminas\Diactoros\ServerRequestFactory;
use Psr\Http\Message\ServerRequestInterface as PsrRequest;
use Tempest\Container\Container;
use Tempest\Http\GenericRequest;
use Tempest\Http\Mappers\RequestToPsrRequestMapper;
use Tempest\Http\Method;
use Tempest\Http\Request;
use Tempest\Router\Exceptions\HttpExceptionHandler;
use Tempest\Router\Router;
use Tempest\Support\Uri;
use Throwable;

use function Tempest\Mapper\map;

final class HttpRouterTester
{
    public function __construct(
        private Container $container,
    ) {}

    public function get(string $uri, array $query = [], array $headers = []): TestResponseHelper
    {
        return $this->sendRequest(
            new GenericRequest(
                method: Method::GET,
                uri: Uri\merge_query($uri, ...$query),
                body: [],
                headers: $headers,
            ),
        );
    }

    public function head(string $uri, array $query = [], array $headers = []): TestResponseHelper
    {
        return $this->sendRequest(
            new GenericRequest(
                method: Method::HEAD,
                uri: Uri\merge_query($uri, ...$query),
                body: [],
                headers: $headers,
            ),
        );
    }

    public function post(string $uri, array $body = [], array $query = [], array $headers = []): TestResponseHelper
    {
        return $this->sendRequest(
            new GenericRequest(
                method: Method::POST,
                uri: Uri\merge_query($uri, ...$query),
                body: $body,
                headers: $headers,
            ),
        );
    }

    public function put(string $uri, array $body = [], array $query = [], array $headers = []): TestResponseHelper
    {
        return $this->sendRequest(
            new GenericRequest(
                method: Method::PUT,
                uri: Uri\merge_query($uri, ...$query),
                body: $body,
                headers: $headers,
            ),
        );
    }

    public function delete(string $uri, array $body = [], array $query = [], array $headers = []): TestResponseHelper
    {
        return $this->sendRequest(
            new GenericRequest(
                method: Method::DELETE,
                uri: Uri\merge_query($uri, ...$query),
                body: $body,
                headers: $headers,
            ),
        );
    }

    public function connect(string $uri, array $query = [], array $headers = []): TestResponseHelper
    {
        return $this->sendRequest(
            new GenericRequest(
                method: Method::CONNECT,
                uri: Uri\merge_query($uri, ...$query),
                body: [],
                headers: $headers,
            ),
        );
    }

    public function options(string $uri, array $query = [], array $headers = []): TestResponseHelper
    {
        return $this->sendRequest(
            new GenericRequest(
                method: Method::OPTIONS,
                uri: Uri\merge_query($uri, ...$query),
                body: [],
                headers: $headers,
            ),
        );
    }

    public function trace(string $uri, array $query = [], array $headers = []): TestResponseHelper
    {
        return $this->sendRequest(
            new GenericRequest(
                method: Method::TRACE,
                uri: Uri\merge_query($uri, ...$query),
                body: [],
                headers: $headers,
            ),
        );
    }

    public function patch(string $uri, array $body = [], array $query = [], array $headers = []): TestResponseHelper
    {
        return $this->sendRequest(
            new GenericRequest(
                method: Method::PATCH,
                uri: Uri\merge_query($uri, ...$query),
                body: $body,
                headers: $headers,
            ),
        );
    }

    public function sendRequest(Request $request): TestResponseHelper
    {
        /** @var Router $router */
        $router = $this->container->get(Router::class);

        try {
            $response = $router->dispatch(map($request)->with(RequestToPsrRequestMapper::class)->do());
        } catch (Throwable $exception) {
            $this->container->get(HttpExceptionHandler::class)->renderResponse($request, $exception);

            return new TestResponseHelper(
                response: $this->container->get(HttpExceptionHandler::class)->renderResponse($request, $exception),
                request: $request,
                container: $this->container,
            );
        }

        return new TestResponseHelper(
            response: $response,
            request: $request,
            container: $this->container,
        );
    }

    public function makePsrRequest(
        string $uri,
        Method $method = Method::GET,
        array $body = [],
        array $headers = [],
        array $cookies = [],
        array $files = [],
    ): PsrRequest {
        $_SERVER['REQUEST_URI'] = $uri;
        $_SERVER['REQUEST_METHOD'] = $method->value;

        foreach ($headers as $key => $value) {
            $key = strtoupper($key);

            $_SERVER["HTTP_{$key}"] = $value;
        }

        $_COOKIE = $cookies;
        $_POST = $body;

        return ServerRequestFactory::fromGlobals()->withUploadedFiles($files);
    }
}
