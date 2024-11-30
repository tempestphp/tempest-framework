<?php

declare(strict_types=1);

namespace Tempest\Router\Tests;

use ReflectionMethod;
use Tempest\Http\Method;
use Tempest\Router\Routing\Construction\DiscoveredRoute;
use Tempest\Router\Routing\Construction\MarkedRoute;
use Tempest\Reflection\MethodReflector;
use Tempest\Router\RouteInterface;

final readonly class FakeRouteBuilder implements RouteInterface
{
    public function __construct(
        public Method $method = Method::GET,
        public string $uri = '/',
    ) {}

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
        $handler = new MethodReflector(new ReflectionMethod($this, 'handler'));
        return DiscoveredRoute::fromRoute($this, $handler);
    }

    public function method(): Method
    {
        return $this->method;
    }

    public function uri(): string
    {
        return $this->uri;
    }

    public function middleware(): array
    {
        return [];
    }

    public function handler(): void {}
}