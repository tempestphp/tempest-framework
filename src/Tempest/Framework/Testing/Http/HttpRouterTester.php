<?php

declare(strict_types=1);

namespace Tempest\Framework\Testing\Http;

use BackedEnum;
use InvalidArgumentException;
use Laminas\Diactoros\ServerRequestFactory;
use Psr\Http\Message\ServerRequestInterface as PsrRequest;
use Tempest\Container\Container;
use Tempest\Http\ContentType;
use Tempest\Http\GenericRequest;
use Tempest\Http\Mappers\RequestToPsrRequestMapper;
use Tempest\Http\Method;
use Tempest\Http\Request;
use Tempest\Reflection\MethodReflector;
use Tempest\Router\Exceptions\HttpExceptionHandler;
use Tempest\Router\Route;
use Tempest\Router\RouteConfig;
use Tempest\Router\RouteDecorator;
use Tempest\Router\Router;
use Tempest\Router\Routing\Construction\DiscoveredRoute;
use Tempest\Router\Routing\Construction\RouteConfigurator;
use Tempest\Router\SecFetchMode;
use Tempest\Router\SecFetchSite;
use Tempest\Router\Static\StaticPageConfig;
use Tempest\Router\StaticPage;
use Tempest\Support\Uri;
use Throwable;

use function Tempest\Mapper\map;

final class HttpRouterTester
{
    private(set) ?ContentType $contentType = null;

    private(set) bool $includeSecFetchHeaders = true;

    public function __construct(
        private Container $container,
    ) {}

    /**
     * Registers a route for testing purposes.
     *
     * @param array{0: class-string, 1: string}|class-string|MethodReflector $action
     */
    public function registerRoute(array|string|MethodReflector $action): self
    {
        $reflector = match (true) {
            $action instanceof MethodReflector => $action,
            is_array($action) => MethodReflector::fromParts(...$action),
            default => MethodReflector::fromParts($action, '__invoke'),
        };

        if ($reflector->getAttribute(Route::class) === null) {
            throw new InvalidArgumentException('Missing route attribute');
        }

        $configurator = $this->container->get(RouteConfigurator::class);

        $configurator->addRoute(
            DiscoveredRoute::fromRoute(
                $reflector->getAttribute(Route::class),
                [
                    ...$reflector->getDeclaringClass()->getAttributes(RouteDecorator::class),
                    ...$reflector->getAttributes(RouteDecorator::class),
                ],
                $reflector,
            ),
        );

        $routeConfig = $this->container->get(RouteConfig::class);
        $routeConfig->apply($configurator->toRouteConfig());

        return $this;
    }

    /**
     * Registers a static page for testing purposes.
     *
     * @param array{0: class-string, 1: string}|class-string|MethodReflector $action
     */
    public function registerStaticPage(array|string|MethodReflector $action): self
    {
        $reflector = match (true) {
            $action instanceof MethodReflector => $action,
            is_array($action) => MethodReflector::fromParts(...$action),
            default => MethodReflector::fromParts($action, '__invoke'),
        };

        if ($reflector->getAttribute(StaticPage::class) === null) {
            throw new InvalidArgumentException('Missing static page attribute');
        }

        $this->container->get(StaticPageConfig::class)->addHandler(
            $reflector->getAttribute(StaticPage::class),
            $reflector,
        );

        return $this;
    }

    /**
     * Specifies the "Accept" header for subsequent requests.
     */
    public function as(ContentType $contentType): self
    {
        $this->contentType = $contentType;

        return $this;
    }

    /**
     * Specifies that subsequent requests should be sent without Sec-Fetch headers.
     */
    public function withoutSecFetchHeaders(): self
    {
        $this->includeSecFetchHeaders = false;

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
        return $this->sendRequest(new GenericRequest(
            method: Method::HEAD,
            uri: Uri\merge_query($uri, ...$query),
            body: [],
            headers: $this->createHeaders($headers),
        ));
    }

    public function post(string $uri, array|string $body = [], array $query = [], array $headers = []): TestResponseHelper
    {
        return $this->sendRequest(new GenericRequest(
            method: Method::POST,
            uri: Uri\merge_query($uri, ...$query),
            body: is_string($body) ? [] : $body,
            headers: $this->createHeaders($headers),
            raw: is_string($body) ? $body : null,
        ));
    }

    public function put(string $uri, array|string $body = [], array $query = [], array $headers = []): TestResponseHelper
    {
        return $this->sendRequest(new GenericRequest(
            method: Method::PUT,
            uri: Uri\merge_query($uri, ...$query),
            body: is_string($body) ? [] : $body,
            headers: $this->createHeaders($headers),
            raw: is_string($body) ? $body : null,
        ));
    }

    public function delete(string $uri, array|string $body = [], array $query = [], array $headers = []): TestResponseHelper
    {
        return $this->sendRequest(new GenericRequest(
            method: Method::DELETE,
            uri: Uri\merge_query($uri, ...$query),
            body: is_string($body) ? [] : $body,
            headers: $this->createHeaders($headers),
            raw: is_string($body) ? $body : null,
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

    public function patch(string $uri, array|string $body = [], array $query = [], array $headers = []): TestResponseHelper
    {
        return $this->sendRequest(new GenericRequest(
            method: Method::PATCH,
            uri: Uri\merge_query($uri, ...$query),
            body: is_string($body) ? [] : $body,
            headers: $this->createHeaders($headers),
            raw: is_string($body) ? $body : null,
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
        array|string $body = [],
        array $headers = [],
        array $cookies = [],
        array $files = [],
    ): PsrRequest {
        $_SERVER['REQUEST_URI'] = $uri;
        $_SERVER['REQUEST_METHOD'] = $method->value;

        foreach ($this->createHeaders($headers) as $key => $value) {
            if ($value instanceof BackedEnum) {
                $value = $value->value;
            }

            $key = strtoupper(str_replace('-', '_', $key));

            $_SERVER["HTTP_{$key}"] = $value;
        }

        $_COOKIE = $cookies;

        if (is_array($body)) {
            $_POST = $body;
        } else {
            $_POST = [];
        }

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

        if ($this->includeSecFetchHeaders === true) {
            if (! array_key_exists('sec-fetch-site', array_change_key_case($headers, case: CASE_LOWER))) {
                $headers['sec-fetch-site'] = SecFetchSite::SAME_ORIGIN;
            }

            if (! array_key_exists('sec-fetch-mode', array_change_key_case($headers, case: CASE_LOWER))) {
                $headers['sec-fetch-mode'] = SecFetchMode::CORS;
            }
        }

        return $headers;
    }
}
