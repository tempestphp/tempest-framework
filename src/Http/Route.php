<?php

declare(strict_types=1);

namespace Tempest\Http;

use Attribute;
use ReflectionMethod;

#[Attribute]
class Route
{
    public ReflectionMethod $handler;

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
        $this->matchingRegex = preg_replace(
            '#\{(\w+)}#',
            '([^/]++)',
            $uri
        );

        $this->isDynamic = $this->matchingRegex !== $this->uri;
    }

    public function setHandler(ReflectionMethod $handler): self
    {
        $this->handler = $handler;

        return $this;
    }

    public function __serialize(): array
    {
        return [
            'uri' => $this->uri,
            'method' => $this->method,
            'middleware' => $this->middleware,
            'handler_class' => $this->handler->getDeclaringClass()->getName(),
            'handler_method' => $this->handler->getName(),
            'matchingRegex' => $this->matchingRegex,
            'isDynamic' => $this->isDynamic,
        ];
    }

    public function __unserialize(array $data): void
    {
        $this->uri = $data['uri'];
        $this->method = $data['method'];
        $this->middleware = $data['middleware'];
        $this->handler = new ReflectionMethod(
            objectOrMethod: $data['handler_class'],
            method: $data['handler_method'],
        );
        $this->matchingRegex = $data['matchingRegex'];
        $this->isDynamic = $data['isDynamic'];
    }
}
