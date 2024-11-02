<?php

declare(strict_types=1);

namespace Tempest\Http;

use Attribute;
use Tempest\Reflection\MethodReflector;

#[Attribute]
class Route
{
    public MethodReflector $handler;

    /** @var bool If the route has params */
    public readonly bool $isDynamic;

    /** @var string[] Route parameters */
    public readonly array $params;

    public const string DEFAULT_MATCHING_GROUP = '[^/]++';

    public const string ROUTE_PARAM_NAME_REGEX = '(\w*)';

    public const string ROUTE_PARAM_CUSTOM_REGEX = '(?::([^{}]*(?:\{(?-1)\}[^{}]*)*))?';

    public function __construct(
        public string $uri,
        public Method $method,

        /**
         * @var class-string<\Tempest\Http\HttpMiddleware>[] $middleware
         */
        public array $middleware = [],
    ) {

        $this->params = self::getRouteParams($this->uri);
        $this->isDynamic = ! empty($this->params);
    }

    public function setHandler(MethodReflector $handler): self
    {
        $this->handler = $handler;

        return $this;
    }

    /** @return string[] */
    public static function getRouteParams(string $uriPart): array
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
            array_filter($parts, static fn (string $part) => $part !== '')
        );
    }
}
