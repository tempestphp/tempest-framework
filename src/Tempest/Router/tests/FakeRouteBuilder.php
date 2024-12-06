<?php

declare(strict_types=1);

namespace Tempest\Router\Tests;

use ReflectionMethod;
use Tempest\Http\Method;
use Tempest\Reflection\MethodReflector;
use Tempest\Router\Route;
use Tempest\Router\Routing\Construction\DiscoveredRoute;
use Tempest\Router\Routing\Construction\MarkedRoute;

final readonly class FakeRouteBuilder implements Route
{
    private MethodReflector $handler;

    public function __construct(
        public Method $method = Method::GET,
        public string $uri = '/',
        public array $middleware = [],
    ) {
        $this->handler = new MethodReflector(new ReflectionMethod($this, 'handler'));
    }

    public function withUri(string $uri): self
    {
        return new self($this->method, $uri);
    }

    public function withMethod(Method $method): self
    {
        return new self($method, $this->uri);
    }

    public function asMarkedRoute(string $mark): MarkedRoute
    {
        return new MarkedRoute($mark, $this->asDiscoveredRoute());
    }

    public function asDiscoveredRoute(): DiscoveredRoute
    {
        return DiscoveredRoute::fromRoute($this, $this->handler);
    }

    public function handler(): void
    {
    }
}
