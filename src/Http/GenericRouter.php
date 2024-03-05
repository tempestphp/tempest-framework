<?php

declare(strict_types=1);

namespace Tempest\Http;

use Closure;
use Psr\Http\Message\ServerRequestInterface as PsrRequest;
use ReflectionClass;
use Tempest\AppConfig;
use function Tempest\attribute;
use Tempest\Container\Container;
use Tempest\Http\Exceptions\InvalidRouteException;
use Tempest\Http\Exceptions\MissingControllerOutputException;
use function Tempest\map;
use function Tempest\response;
use Tempest\View\View;

final readonly class GenericRouter implements Router
{
    private const string MARK_TOKEN = 'MARK';

    public function __construct(
        private Container $container,
        private AppConfig $appConfig,
        private RouteConfig $routeConfig,
    ) {
    }

    /**
     * @return \Tempest\Http\Route[][]
     */
    public function getRoutes(): array
    {
        return $this->routeConfig->routes;
    }

    public function dispatch(PsrRequest $request): Response
    {
        $matchedRoute = $this->matchRoute($request);

        if ($matchedRoute === null) {
            return response()->notFound();
        }

        $this->container->singleton(
            MatchedRoute::class,
            fn () => $matchedRoute,
        );

        $callable = $this->getCallable($matchedRoute);

        $request = $this->prepareRequest($request, $matchedRoute);

        $outputFromController = $callable($request);

        if ($outputFromController === null) {
            throw new MissingControllerOutputException(
                $matchedRoute->route->handler->getDeclaringClass()->getName(),
                $matchedRoute->route->handler->getName(),
            );
        }

        return $this->createResponse($outputFromController);
    }

    private function matchRoute(PsrRequest|Request $request): ?MatchedRoute
    {
        // Try to match routes without any parameters
        if ($staticRoute = $this->matchStaticRoute($request)) {
            return $staticRoute;
        }

        // match dynamic routes
        return $this->matchDynamicRoute($request);
    }

    private function getCallable(MatchedRoute $matchedRoute): Closure
    {
        $route = $matchedRoute->route;

        $callable = fn (Request $request) => $this->container->call(
            $this->container->get($route->handler->getDeclaringClass()->getName()),
            $route->handler->getName(),
            ...$matchedRoute->params,
        );

        $middlewareStack = $route->middleware;

        while ($middlewareClass = array_pop($middlewareStack)) {
            $callable = fn (Request $request) => $this->container->get($middlewareClass)($request, $callable);
        }

        return $callable;
    }

    public function toUri(array|string $action, ...$params): string
    {
        if (is_array($action)) {
            $controllerClass = $action[0];
            $reflection = new ReflectionClass($controllerClass);
            $controllerMethod = $reflection->getMethod($action[1]);
        } else {
            $controllerClass = $action;
            $reflection = new ReflectionClass($controllerClass);
            $controllerMethod = $reflection->getMethod('__invoke');
        }

        $routeAttribute = attribute(Route::class)->in($controllerMethod)->first();

        if (! $routeAttribute) {
            throw new InvalidRouteException($controllerClass, $controllerMethod->getName());
        }

        $uri = $routeAttribute->uri;

        foreach ($params as $key => $value) {
            $uri = str_replace('{' . $key . '}', "{$value}", $uri);
        }

        return $uri;
    }

    private function resolveParams(Route $route, string $uri): ?array
    {
        if ($route->uri === $uri) {
            return [];
        }

        $tokensResult = preg_match_all('#\{\w+}#', $route->uri, $tokens);

        if (! $tokensResult) {
            return null;
        }

        $tokens = $tokens[0];

        $tokensResult = preg_match_all("#^$route->matchingRegex$#", $uri, $matches);

        if ($tokensResult === 0) {
            return null;
        }

        unset($matches[0]);

        $matches = array_values($matches);

        $valueMap = [];

        foreach ($matches as $i => $match) {
            $valueMap[trim($tokens[$i], '{}')] = $match[0];
        }

        return $valueMap;
    }

    private function createResponse(Response|View $input): Response
    {
        if ($input instanceof View) {
            return response($input->render($this->appConfig));
        }

        if ($view = $input->getView()) {
            $input->body($view->render($this->appConfig));
        }

        return $input;
    }

    private function matchStaticRoute(PsrRequest $request): ?MatchedRoute
    {
        $staticRoute = $this->getRoutes()[$request->getMethod()][$request->getUri()->getPath()] ?? null;

        if ($staticRoute === null) {
            return null;
        }

        // TODO: why do we need to check this?
        if ($staticRoute->isDynamic) {
            return null;
        }

        return new MatchedRoute($staticRoute, []);
    }

    private function matchDynamicRoute(PsrRequest $request): ?MatchedRoute
    {
        // If there are no routes for the given request method, we immediately stop
        $routesForMethod = $this->getRoutes()[$request->getMethod()] ?? null;
        if ($routesForMethod === null) {
            return null;
        }

        // Next, we'll build one big regex to match the correct route
        // See https://github.com/tempestphp/tempest-framework/pull/175 for the details

        /** @var \Tempest\Http\Route[] $routesForMethod */
        $routesForMethod = array_values($routesForMethod);
        $combinedMatchingRegex = "#^(?|";

        foreach ($routesForMethod as $routeIndex => $route) {
            if (! $route->isDynamic) {
                continue; // TODO: why this check?
            }

            $combinedMatchingRegex .= "$route->matchingRegex (*" . self::MARK_TOKEN . ":$routeIndex) |";
        }

        $combinedMatchingRegex = rtrim($combinedMatchingRegex, '|');
        $combinedMatchingRegex .= ')$#x';

        // Then we'll use this regex to see whether we have a match or not
        $matchResult = preg_match($combinedMatchingRegex, $request->getUri()->getPath(), $matches);

        if (! $matchResult || ! array_key_exists(self::MARK_TOKEN, $matches)) {
            return null;
        }

        $route = $routesForMethod[$matches[self::MARK_TOKEN]];

        // TODO: we could probably optimize resolveParams now,
        // because we already know for sure there's a match
        $routeParams = $this->resolveParams($route, $request->getUri()->getPath());

        // This check should _in theory_ not be needed,
        // since we're certain there's a match
        if ($routeParams === null) {
            return null;
        }

        return new MatchedRoute($route, $routeParams);
    }

    private function prepareRequest(PsrRequest $psrRequest, MatchedRoute $matchedRoute): Request
    {
        // Let's find out if our input request data matches what the route's action needs
        $requestClass = GenericRequest::class;

        // We'll loop over all the handler's parameters
        foreach ($matchedRoute->route->handler->getParameters() as $parameter) {
            // TODO: support unions

            // If the parameter's type is an instance of Request…
            if (is_a($parameter->getType()->getName(), Request::class, true)) {
                // We'll use that specific request class
                $requestClass = $parameter->getType()->getName();

                break;
            }
        }

        // We map the original request we got into this method to the right request class
        $request = map($psrRequest)->to($requestClass);

        // Finally, we register this newly created request object in the container
        // This makes it so that RequestInitializer is bypassed entirely when the controller action needs the request class
        // Making it so that we don't need to set any $_SERVER variables and stuff like that
        $this->container->singleton(Request::class, fn () => $request);
        $this->container->singleton($request::class, fn () => $request);

        return $request;
    }
}
