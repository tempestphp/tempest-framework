<?php

declare(strict_types=1);

namespace Tempest\Router;

use Psr\Http\Message\ServerRequestInterface as PsrRequest;

interface Router
{
    public function dispatch(Request|PsrRequest $request): Response;

    public function toUri(array|string $action, ...$params): string;

    /**
     * @template T of \Tempest\Router\HttpMiddleware
     * @param class-string<T> $middlewareClass
     */
    public function addMiddleware(string $middlewareClass): void;
}
