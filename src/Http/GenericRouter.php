<?php

declare(strict_types=1);

namespace Tempest\Http;

use ReflectionClass;
use Tempest\AppConfig;

use function Tempest\attribute;

use Tempest\Container\InitializedBy;
use Tempest\Http\Exceptions\InvalidRouteException;
use Tempest\Http\Exceptions\MissingControllerOutputException;
use Tempest\Interface\Container;
use Tempest\Interface\Request;
use Tempest\Interface\Response;
use Tempest\Interface\Router;

use Tempest\Interface\View;

use function Tempest\response;

#[InitializedBy(RouteInitializer::class)]
final readonly class GenericRouter implements Router
{
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

    public function dispatch(Request $request): Response
    {
        $actionsForMethod = $this->getRoutes()[$request->method->value] ?? null;

        if (! $actionsForMethod) {
            return response()->notFound();
        }

        $routeParams = null;
        $matchedRoute = null;

        foreach ($actionsForMethod as $route) {
            $routeParams = $this->resolveParams($route->uri, $request->getPath());

            if ($routeParams !== null) {
                $matchedRoute = $route;

                break;
            }
        }

        if ($routeParams === null) {
            // TODO: not found
        }

        if ($routeParams === null) {
            return response()->notFound();
        }

        $this->container->singleton(
            RouteParams::class,
            fn () => new RouteParams($routeParams),
        );

        $controller = $this->container->get(
            $matchedRoute->handler->getDeclaringClass()->getName()
        );

        $outputFromController = $this->container->call(
            $controller,
            $matchedRoute->handler->getName(),
            ...$routeParams
        );

        if ($outputFromController === null) {
            throw new MissingControllerOutputException(
                $matchedRoute->handler->getDeclaringClass()->getName(),
                $matchedRoute->handler->getName(),
            );
        }

        return $this->createResponse($outputFromController);
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

    private function resolveParams(string $pattern, string $uri): ?array
    {
        if ($pattern === $uri) {
            return [];
        }

        $result = preg_match_all('/\{\w+}/', $pattern, $tokens);

        if (! $result) {
            return null;
        }

        $tokens = $tokens[0];

        $matchingRegex = '/^' . str_replace(
            ['/', ...$tokens],
            ['\\/', ...array_fill(0, count($tokens), '([\w\d\s]+)')],
            $pattern,
        ) . '$/';

        $result = preg_match_all($matchingRegex, $uri, $matches);

        if ($result === 0) {
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

        return $input;
    }
}
