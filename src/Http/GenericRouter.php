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
        $class = new ReflectionClass($controller);

        foreach ($class->getMethods() as $controllerMethod) {
            $routeAttribute = attribute(Route::class)
                ->in($controllerMethod)
                ->first();

            if (! $routeAttribute) {
                continue;
            }

            $this->registerRoute(
                $routeAttribute,
                $controller,
                $controllerMethod->getName(),
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
            $routeParams = $this->resolveParams($pattern, $request->getPath());

            if ($routeParams !== null) {
                $matchedAction = $action;

                break;
            }
        }

        if ($routeParams === null) {
            return response()->notFound();
        }

        $this->container->singleton(
            RouteParams::class,
            fn () => new RouteParams($routeParams),
        );

        [$controllerClass, $controllerMethod] = $matchedAction;

        $controller = $this->container->get($controllerClass);

        $outputFromController = $this->container->call($controller, $controllerMethod, ...$routeParams);

        if ($outputFromController === null) {
            throw new MissingControllerOutputException($controllerClass, $controllerMethod);
        }

        return $this->createResponse($outputFromController);
    }

    public function toUri(
        array|string $action,
        ...$params,
    ): string {
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
