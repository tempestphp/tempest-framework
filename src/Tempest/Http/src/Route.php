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
         * @template MiddlewareClass of \Tempest\Http\HttpMiddleware
         * @var class-string<MiddlewareClass>[] $middleware
         */
        public array $middleware = [],
    ) {

        // Routes can have parameters in the form of "/{PARAM}/" or /{PARAM:CUSTOM_REGEX},
        // these parameters are replaced with a regex matching group or with the custom regex
        $matchingRegex = (string)str($this->uri)->replaceRegex(
            '#\{'. self::ROUTE_PARAM_NAME_REGEX . self::ROUTE_PARAM_CUSTOM_REGEX .'\}#',
            fn ($matches) => '(' . trim(arr($matches)->get('2', self::DEFAULT_MATCHING_GROUP)). ')'
        );

        $this->isDynamic = $matchingRegex !== $this->uri;

        // Allow for optional trailing slashes
        $this->matchingRegex = $matchingRegex . '\/?';
    }

    public function setHandler(MethodReflector $handler): self
    {
        $this->handler = $handler;

        return $this;
    }
}
