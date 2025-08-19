<?php

declare(strict_types=1);

namespace Tempest\Router\Tests;

use PHPUnit\Framework\TestCase;
use Tempest\Container\Container;
use Tempest\Container\GenericContainer;
use Tempest\Http\GenericRequest;
use Tempest\Http\Method;
use Tempest\Http\Request;
use Tempest\Http\Response;
use Tempest\Http\Responses\NotFound;
use Tempest\Http\Responses\Ok;
use Tempest\Router\HttpMiddlewareCallable;
use Tempest\Router\MatchedRoute;
use Tempest\Router\MatchRouteMiddleware;
use Tempest\Router\RouteConfig;
use Tempest\Router\Routing\Matching\GenericRouteMatcher;

final class MatchRouteMiddlewareTest extends TestCase
{
    private Container $container;
    private RouteConfig $routeConfig;
    private MatchRouteMiddleware $middleware;

    protected function setUp(): void
    {
        parent::setUp();

        $this->container = new GenericContainer();
        $this->routeConfig = new RouteConfig();
        $this->container->singleton(RouteConfig::class, fn () => $this->routeConfig);
        $routeMatcher = new GenericRouteMatcher($this->routeConfig);
        $this->middleware = new MatchRouteMiddleware(
            routeMatcher: $routeMatcher,
            container: $this->container,
        );
    }

    public function test_method_spoofing_with_put(): void
    {
        $this->addRoute(Method::PUT, '/users/1');

        $request = new GenericRequest(
            method: Method::POST,
            uri: '/users/1',
            body: ['_method' => 'PUT', 'name' => 'John'],
        );

        $next = new HttpMiddlewareCallable(
            fn (Request $_request) => new Ok('Middleware processed'),
        );

        $response = ($this->middleware)($request, $next);
        $matchedRoute = $this->container->get(MatchedRoute::class);

        $this->assertInstanceOf(Ok::class, $response);
        $this->assertNotNull($matchedRoute);
        $this->assertEquals(Method::PUT, $matchedRoute->route->method);
    }

    public function test_method_spoofing_with_patch(): void
    {
        $this->addRoute(Method::PATCH, '/users/1');

        $request = new GenericRequest(
            method: Method::POST,
            uri: '/users/1',
            body: ['_method' => 'PATCH'],
        );

        $next = new HttpMiddlewareCallable(
            fn (Request $_request) => new Ok('Middleware processed'),
        );

        $response = ($this->middleware)($request, $next);
        $matchedRoute = $this->container->get(MatchedRoute::class);

        $this->assertInstanceOf(Ok::class, $response);
        $this->assertNotNull($matchedRoute);
        $this->assertEquals(Method::PATCH, $matchedRoute->route->method);
    }

    public function test_method_spoofing_with_delete(): void
    {
        $this->addRoute(Method::DELETE, '/users/1');

        $request = new GenericRequest(
            method: Method::POST,
            uri: '/users/1',
            body: ['_method' => 'DELETE'],
        );

        $next = new HttpMiddlewareCallable(
            fn (Request $_request) => new Ok('Middleware processed'),
        );

        $response = ($this->middleware)($request, $next);
        $matchedRoute = $this->container->get(MatchedRoute::class);

        $this->assertInstanceOf(Ok::class, $response);
        $this->assertNotNull($matchedRoute);
        $this->assertEquals(Method::DELETE, $matchedRoute->route->method);
    }

    public function test_method_spoofing_with_lowercase_method(): void
    {
        $this->addRoute(Method::PUT, '/users/1');

        $request = new GenericRequest(
            method: Method::POST,
            uri: '/users/1',
            body: ['_method' => 'put'],
        );

        $next = new HttpMiddlewareCallable(
            fn (Request $_request) => new Ok('Middleware processed'),
        );

        $response = ($this->middleware)($request, $next);
        $matchedRoute = $this->container->get(MatchedRoute::class);

        $this->assertInstanceOf(Ok::class, $response);
        $this->assertNotNull($matchedRoute);
        $this->assertEquals(Method::PUT, $matchedRoute->route->method);
    }

    public function test_method_spoofing_ignores_invalid_method(): void
    {
        $this->addRoute(Method::POST, '/users/1');

        $request = new GenericRequest(
            method: Method::POST,
            uri: '/users/1',
            body: ['_method' => 'INVALID'],
        );

        $next = new HttpMiddlewareCallable(
            fn (Request $_request) => new Ok('Middleware processed'),
        );

        $response = ($this->middleware)($request, $next);
        $matchedRoute = $this->container->get(MatchedRoute::class);

        $this->assertInstanceOf(Ok::class, $response);
        $this->assertNotNull($matchedRoute);
        $this->assertEquals(Method::POST, $matchedRoute->route->method);
    }

    public function test_method_spoofing_not_allowed_for_get(): void
    {
        $this->addRoute(Method::GET, '/users/1');
        $this->addRoute(Method::POST, '/users/1');

        $request = new GenericRequest(
            method: Method::POST,
            uri: '/users/1',
            body: ['_method' => 'GET'],
        );

        $next = new HttpMiddlewareCallable(
            fn (Request $_request) => new Ok('Middleware processed'),
        );

        $response = ($this->middleware)($request, $next);
        $matchedRoute = $this->container->get(MatchedRoute::class);

        $this->assertInstanceOf(Ok::class, $response);
        $this->assertNotNull($matchedRoute);
        $this->assertEquals(Method::POST, $matchedRoute->route->method);
    }

    public function test_method_spoofing_only_applies_to_post(): void
    {
        $this->addRoute(Method::PUT, '/users/1');

        $request = new GenericRequest(
            method: Method::GET,
            uri: '/users/1',
            body: ['_method' => 'PUT'],
        );

        $next = new HttpMiddlewareCallable(
            fn (Request $_request) => new Ok('Middleware processed'),
        );

        $response = ($this->middleware)($request, $next);

        $this->assertInstanceOf(NotFound::class, $response);
    }

    public function test_no_spoofing_without_method_parameter(): void
    {
        $this->addRoute(Method::POST, '/users/1');

        $request = new GenericRequest(
            method: Method::POST,
            uri: '/users/1',
            body: ['name' => 'John'],
        );

        $next = new HttpMiddlewareCallable(
            fn (Request $_request) => new Ok('Middleware processed'),
        );

        $response = ($this->middleware)($request, $next);
        $matchedRoute = $this->container->get(MatchedRoute::class);

        $this->assertInstanceOf(Ok::class, $response);
        $this->assertNotNull($matchedRoute);
        $this->assertEquals(Method::POST, $matchedRoute->route->method);
    }

    public function test_head_to_get_fallback_still_works(): void
    {
        $this->addRoute(Method::GET, '/users');

        $request = new GenericRequest(
            method: Method::HEAD,
            uri: '/users',
        );

        $next = new HttpMiddlewareCallable(
            fn (Request $_request) => new Ok('Middleware processed'),
        );

        $response = ($this->middleware)($request, $next);
        $matchedRoute = $this->container->get(MatchedRoute::class);

        $this->assertInstanceOf(Ok::class, $response);
        $this->assertNotNull($matchedRoute);
        $this->assertEquals(Method::GET, $matchedRoute->route->method);
    }

    public function test_method_spoofing_preserves_request_data(): void
    {
        $this->addRoute(Method::PUT, '/users/1');

        $request = new GenericRequest(
            method: Method::POST,
            uri: '/users/1',
            body: [
                '_method' => 'PUT',
                'name' => 'John',
                'email' => 'john@example.com',
            ],
        );

        $processedRequest = null;
        $next = new HttpMiddlewareCallable(
            function (Request $request) use (&$processedRequest) {
                $processedRequest = $request;
                return new Ok('Middleware processed');
            },
        );

        ($this->middleware)($request, $next);

        $this->assertNotNull($processedRequest);
        $this->assertEquals('John', $processedRequest->get('name'));
        $this->assertEquals('john@example.com', $processedRequest->get('email'));
        $this->assertEquals('PUT', $processedRequest->get('_method'));
    }

    private function addRoute(Method $method, string $uri): void
    {
        $route = new FakeRouteBuilder();
        $route = $route
            ->withMethod($method)
            ->withUri($uri)
            ->asDiscoveredRoute();

        if ($route->isDynamic) {
            $this->routeConfig->dynamicRoutes[$method->value][$route->markName] = $route;
        } else {
            $this->routeConfig->staticRoutes[$method->value][$uri] = $route;
        }
    }
}
