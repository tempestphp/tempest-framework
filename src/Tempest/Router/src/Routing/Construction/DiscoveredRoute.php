<?php

declare(strict_types=1);

namespace Tempest\Router\Routing\Construction;

use Tempest\Http\Method;
use Tempest\Reflection\MethodReflector;
use Tempest\Router\HttpMiddleware;
use Tempest\Router\Route;

final class DiscoveredRoute implements Route
{
    public const string DEFAULT_MATCHING_GROUP = '[^/]++';

    public const string ROUTE_PARAM_NAME_REGEX = '(\w*)';

    public const string ROUTE_PARAM_CUSTOM_REGEX = '(?::([^{}]*(?:\{(?-1)\}[^{}]*)*))?';

    public static function fromRoute(Route $route, MethodReflector $methodReflector): self
    {
        return new self(
            $route->uri,
            $route->method,
            self::getRouteParams($route->uri),
            $route->middleware,
            $methodReflector,
        );
    }

    public readonly bool $isDynamic;

    private function __construct(
        public readonly string $uri,
        public readonly Method $method,
        public readonly array $parameters,
        /** @var class-string<HttpMiddleware>[] */
        public readonly array $middleware,
        public readonly MethodReflector $handler,
    ) {
        $this->isDynamic = $parameters !== [];
    }

    /** @return string[] */
    private static function getRouteParams(string $uriPart): array
    {
        $regex = '#\{'. self::ROUTE_PARAM_NAME_REGEX . self::ROUTE_PARAM_CUSTOM_REGEX .'\}#';

        preg_match_all($regex, $uriPart, $matches);

        return $matches[1] ?? [];
    }

    /**
     * Splits the route URI into separate segments
     *
     * @example '/test/{id}/edit' becomes ['test', '{id}', 'edit']
     * @return string[]
     */
    public function split(): array
    {
        $parts = explode('/', $this->uri);

        return array_values(
            array_filter($parts, static fn (string $part) => $part !== ''),
        );
    }
}
