<?php

declare(strict_types=1);

namespace Tempest\Http;

use Exception;
use ReflectionAttribute;
use ReflectionClass;
use Tempest\AppConfig;
use Tempest\Container\InitializedBy;
use Tempest\Interfaces\Container;
use Tempest\Interfaces\Request;
use Tempest\Interfaces\Response;
use Tempest\Interfaces\Router;
use Tempest\Interfaces\View;

#[InitializedBy(RouteInitializer::class)]
final class GenericRouter implements Router
{
    public function __construct(
        private readonly Container $container,
        private readonly AppConfig $appConfig,
        private array $routes = [],
    ) {
    }

    public function registerController(string $controller): self
    {
        $reflection = new ReflectionClass($controller);

        foreach ($reflection->getMethods() as $method) {
            $routeAttribute = ($method->getAttributes(Route::class, ReflectionAttribute::IS_INSTANCEOF)[0] ?? null);

            if (! $routeAttribute) {
                continue;
            }

            $this->registerRoute(
                $routeAttribute->newInstance(),
                $controller,
                $method->getName(),
            );
        }

        return $this;
    }

    public function registerRoute(Route $route, string $controllerClass, string $controllerMethod): self
    {
        $this->routes[$route->method->value][$route->uri] = [$controllerClass, $controllerMethod];

        return $this;
    }

    public function dispatch(Request $request): Response
    {
        $actionsForMethod = $this->routes[$request->method->value] ?? null;

        if (! $actionsForMethod) {
            return response()->notFound();
        }

        $routeParams = null;
        $matchedAction = null;

        foreach ($actionsForMethod as $pattern => $action) {
            $routeParams = $this->resolveParams($pattern, $request->uri);

            if ($routeParams !== null) {
                $matchedAction = $action;

                break;
            }
        }

        if ($routeParams === null) {
            return response()->notFound();
        }

        [$controllerClass, $controllerMethod] = $matchedAction;

        $controller = $this->container->get($controllerClass);

        $outputFromController = $this->container->call($controller, $controllerMethod, ...$routeParams);

        if ($outputFromController === null) {
            throw new Exception("{$controllerClass}::{$controllerMethod}() did not return anything");
        }

        return $this->createResponse($outputFromController);
    }

    public function toUri(
        string $controller,
        ?string $method = null,
        ...$params,
    ): string {
        $reflection = new ReflectionClass($controller);

        $method = $reflection->getMethod($method ?? '__invoke');

        $routeAttribute = ($method->getAttributes(Route::class, ReflectionAttribute::IS_INSTANCEOF)[0] ?? null);

        if (! $routeAttribute) {
            throw new Exception("No route found");
        }

        $uri = $routeAttribute->newInstance()->uri;

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
