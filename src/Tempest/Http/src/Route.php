<?php

declare(strict_types=1);

namespace Tempest\Http;

use Attribute;
use Tempest\Support\Reflection\MethodReflector;

#[Attribute]
class Route
{
    public MethodReflector $handler;

    /** @var string The Regex used for matching this route against a request URI */
    public readonly string $matchingRegex;

    /** @var bool If the route has params */
    public readonly bool $isDynamic;

    public function __construct(
        public string $uri,
        public Method $method,

        /**
         * @template MiddlewareClass of \Tempest\Http\HttpMiddleware
         * @var class-string<MiddlewareClass>[] $middleware
         */
        public array $middleware = [],
    ) {
        // Routes can have parameters in the form of "/{PARAM}/",
        // these parameters are replaced with a regex matching group
        $matchingRegex = preg_replace(
            '#\{(\w+)}#',
            '([^/]++)',
            $uri
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
