<?php

declare(strict_types=1);

namespace Tempest\Router;

use Tempest\Http\Method;
use Tempest\Reflection\MethodReflector;

trait IsRoute
{
    private Method $method;
    private string $uri;

    /** @var class-string<HttpMiddleware>[] $middleware */
    private array $middleware;

    private MethodReflector $handler;

    public function method(): Method
    {
        return $this->method;
    }

    public function uri(): string
    {
        return $this->uri;
    }

    /** @return class-string<HttpMiddleware>[] */
    public function middleware(): array
    {
        return $this->middleware;
    }
}
