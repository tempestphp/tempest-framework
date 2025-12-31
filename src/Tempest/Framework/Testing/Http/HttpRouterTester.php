<?php

declare(strict_types=1);

namespace Tempest\Framework\Testing\Http;

use Laminas\Diactoros\ServerRequestFactory;
use Psr\Http\Message\ServerRequestInterface as PsrRequest;
use Tempest\Container\Container;
use Tempest\Http\ContentType;
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
    private(set) ?ContentType $contentType = null;

    public function __construct(
        private Container $container,
    ) {}

    /**
     * Specifies the "Accept" header for subsequent requests.
     */
    public function as(ContentType $contentType): self
    {
        $this->contentType = $contentType;

        return $this;
    }

    public function get(string $uri, array $query = [], array $headers = []): TestResponseHelper
    {
        return $this->sendRequest(new GenericRequest(
            method: Method::GET,
            uri: Uri\merge_query($uri, ...$query),
            body: [],
            headers: $this->createHeaders($headers),
        ));
    }

    public function head(string $uri, array $query = [], array $headers = []): TestResponseHelper
    {
        return $this->sendRequest(
            new GenericRequest(
                method: Method::HEAD,
                uri: Uri\merge_query($uri, ...$query),
                body: [],
                headers: $this->createHeaders($headers),
            ),
        );
    }

    public function post(string $uri, array $body = [], array $query = [], array $headers = []): TestResponseHelper
    {
        return $this->sendRequest(new GenericRequest(
            method: Method::POST,
            uri: Uri\merge_query($uri, ...$query),
            body: $body,
            headers: $this->createHeaders($headers),
        ));
    }

    public function put(string $uri, array $body = [], array $query = [], array $headers = []): TestResponseHelper
    {
        return $this->sendRequest(new GenericRequest(
            method: Method::PUT,
            uri: Uri\merge_query($uri, ...$query),
            body: $body,
            headers: $this->createHeaders($headers),
        ));
    }

    public function delete(string $uri, array $body = [], array $query = [], array $headers = []): TestResponseHelper
    {
        return $this->sendRequest(new GenericRequest(
            method: Method::DELETE,
            uri: Uri\merge_query($uri, ...$query),
            body: $body,
            headers: $this->createHeaders($headers),
        ));
    }

    public function connect(string $uri, array $query = [], array $headers = []): TestResponseHelper
    {
        return $this->sendRequest(new GenericRequest(
            method: Method::CONNECT,
            uri: Uri\merge_query($uri, ...$query),
            body: [],
            headers: $this->createHeaders($headers),
        ));
    }

    public function options(string $uri, array $query = [], array $headers = []): TestResponseHelper
    {
        return $this->sendRequest(new GenericRequest(
            method: Method::OPTIONS,
            uri: Uri\merge_query($uri, ...$query),
            body: [],
            headers: $this->createHeaders($headers),
        ));
    }

    public function trace(string $uri, array $query = [], array $headers = []): TestResponseHelper
    {
        return $this->sendRequest(new GenericRequest(
            method: Method::TRACE,
            uri: Uri\merge_query($uri, ...$query),
            body: [],
            headers: $this->createHeaders($headers),
        ));
    }

    public function patch(string $uri, array $body = [], array $query = [], array $headers = []): TestResponseHelper
    {
        return $this->sendRequest(new GenericRequest(
            method: Method::PATCH,
            uri: Uri\merge_query($uri, ...$query),
            body: $body,
            headers: $this->createHeaders($headers),
        ));
    }

    public function sendRequest(Request $request): TestResponseHelper
    {
        /** @var Router $router */
        $router = $this->container->get(Router::class);

        try {
            $response = $router->dispatch(map($request)->with(RequestToPsrRequestMapper::class)->do());
        } catch (Throwable $throwable) {
            return new TestResponseHelper(
                response: $this->container->get(HttpExceptionHandler::class)->renderResponse($request, $throwable),
                request: $request,
                container: $this->container,
                throwable: $throwable,
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

    private function createHeaders(array $headers = []): array
    {
        $key = array_find_key(
            array: $headers,
            callback: fn (mixed $_, string $headerKey): bool => strcasecmp($headerKey, 'accept') === 0,
        );

        if ($this->contentType !== null) {
            $headers[$key ?? 'accept'] = $this->contentType->value;
        }

        return $headers;
    }
}
