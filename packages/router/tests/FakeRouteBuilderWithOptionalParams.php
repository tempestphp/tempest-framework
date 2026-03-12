<?php

declare(strict_types=1);

namespace Tempest\Router\Tests;

use ReflectionMethod;
use Tempest\Http\Method;
use Tempest\Reflection\MethodReflector;
use Tempest\Router\HttpMiddleware;
use Tempest\Router\Route;
use Tempest\Router\Routing\Construction\DiscoveredRoute;
use Tempest\Router\Routing\Construction\MarkedRoute;

final class FakeRouteBuilderWithOptionalParams implements Route
{
    private MethodReflector $handler;

    public function __construct(
        public Method $method = Method::GET,
        public string $uri = '/',
        /** @var class-string<HttpMiddleware>[] */
        public array $middleware = [],
        /** @var class-string<HttpMiddleware>[] */
        public array $without = [],
        private string $handlerMethod = 'handler',
    ) {
        $this->handler = new MethodReflector(new ReflectionMethod($this, $this->handlerMethod));
    }

    public function withUri(string $uri): self
    {
        return new self($this->method, $uri, $this->middleware, $this->without, $this->handlerMethod);
    }

    public function withMethod(Method $method): self
    {
        return new self($method, $this->uri, $this->middleware, $this->without, $this->handlerMethod);
    }

    public function withHandler(string $handlerMethod): self
    {
        return new self($this->method, $this->uri, $this->middleware, $this->without, $handlerMethod);
    }

    public function asMarkedRoute(string $mark): MarkedRoute
    {
        return new MarkedRoute($mark, $this->asDiscoveredRoute());
    }

    public function asDiscoveredRoute(): DiscoveredRoute
    {
        return DiscoveredRoute::fromRoute($this, [], $this->handler);
    }

    public function handler(): void
    {
    }

    public function handlerWithOptionalId(string $id = 'default-id'): void
    {
    }

    public function handlerWithTwoOptionalParams(string $id = 'default-id', string $slug = 'default-slug'): void
    {
    }

    public function handlerWithRequiredAndOptional(string $id, string $slug = 'default-slug'): void
    {
    }
}
