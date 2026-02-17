<?php

declare(strict_types=1);

namespace Tempest\Router\Routing\Construction;

use Tempest\Http\Method;
use Tempest\Reflection\MethodReflector;
use Tempest\Router\Route;

final class DiscoveredRoute implements Route
{
    public const string DEFAULT_MATCHING_GROUP = '[^/]++';

    public const string ROUTE_PARAM_OPTIONAL_REGEX = '(\??)';

    public const string ROUTE_PARAM_NAME_REGEX = '(\w*)';

    public const string ROUTE_PARAM_CUSTOM_REGEX = '(?::([^{}]*(?:\{(?-1)\}[^{}]*)*))?';

    /** @param \Tempest\Router\RouteDecorator[] $decorators */
    public static function fromRoute(Route $route, array $decorators, MethodReflector $methodReflector): self
    {
        foreach ($decorators as $decorator) {
            $route = $decorator->decorate($route);
        }

        $uri = self::parseUriAndParameters($route->uri, $methodReflector);

        return new self(
            uri: $uri['uri'],
            method: $route->method,
            parameters: $uri['names'],
            optionalParameters: $uri['optional'],
            middleware: $route->middleware,
            handler: $methodReflector,
            without: $route->without,
        );
    }

    public bool $isDynamic;

    private function __construct(
        public string $uri,
        public Method $method,
        public array $parameters,
        /** @var array<string, bool> */
        public array $optionalParameters,
        /** @var class-string<\Tempest\Router\HttpMiddleware>[] */
        public array $middleware,
        public MethodReflector $handler,
        /** @var class-string<\Tempest\Router\HttpMiddleware>[] */
        public array $without = [],
    ) {
        $this->isDynamic = $parameters !== [];
    }

    /**
     * Parses route parameters and infers type constraints from method signature.
     *
     * @return array{
     *     uri: string,
     *     names: string[],
     *     optional: array<string, bool>
     * }
     */
    private static function parseUriAndParameters(string $uri, MethodReflector $methodReflector): array
    {
        $regex = '#\{' . self::ROUTE_PARAM_OPTIONAL_REGEX . self::ROUTE_PARAM_NAME_REGEX . self::ROUTE_PARAM_CUSTOM_REGEX . '\}#';

        $names = [];
        $optional = [];

        $modifiedUri = preg_replace_callback(
            pattern: $regex,
            callback: static function (array $matches) use ($methodReflector, &$names, &$optional) {
                $isOptional = $matches[1] === '?';
                $paramName = $matches[2];
                $providedRegExp = $matches[3] ?? '';

                $names[] = $paramName;
                $optional[$paramName] = $isOptional;

                // Skip if there was already a constraint
                if ($providedRegExp !== '') {
                    return $matches[0];
                }

                $parameter = $methodReflector->getParameter($paramName);

                if ($parameter === null) {
                    return $matches[0];
                }

                $constraint = match ($parameter->getType()->getName()) {
                    'int', 'integer' => ':\d+',
                    'float', 'double' => ':[\d.]+',
                    'bool', 'boolean' => ':(0|1|true|false)',
                    default => null,
                };

                if ($constraint !== null) {
                    return sprintf('{%s%s%s}', $isOptional ? '?' : '', $paramName, $constraint);
                }

                return $matches[0];
            },
            subject: $uri,
        );

        return [
            'uri' => $modifiedUri,
            'names' => $names,
            'optional' => $optional,
        ];
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
