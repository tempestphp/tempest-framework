<?php

declare(strict_types=1);

namespace Tempest\Http;

use Attribute;
use Tempest\Reflection\MethodReflector;
use function Tempest\Support\arr;
use function Tempest\Support\str;

#[Attribute]
class Route
{
    public MethodReflector $handler;

    /** @var string The Regex used for matching this route against a request URI */
    public readonly string $matchingRegex;

    /** @var bool If the route has params */
    public readonly bool $isDynamic;

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
        $this->isDynamic = Route::isDynamic($this->uri);
    }

    public function setHandler(MethodReflector $handler): self
    {
        $this->handler = $handler;

        return $this;
    }

    public static function isDynamic(string $uriPart): bool
    {
        $regex = '#\{'. self::ROUTE_PARAM_NAME_REGEX . self::ROUTE_PARAM_CUSTOM_REGEX .'\}#';
        return preg_match($regex, $uriPart) === 1;
    }

    /** @return string[] */
    public function routeParts(): array
    {
        $parts = explode('/', $this->uri);

        return array_filter($parts, static fn (string $part) => !empty($part));
    }
}
