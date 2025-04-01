<?php

declare(strict_types=1);

namespace Tempest\Router;

use Psr\Http\Message\ServerRequestInterface as PsrRequest;

interface Router
{
    public function dispatch(Request|PsrRequest $request): Response;

    /**
     * Creates a valid URI to the given `$action`.
     */
    public function toUri(array|string $action, ...$params): string;

    /**
     * Checks if the URI to the given `$action` would match the current route.
     */
    public function isCurrentUri(array|string $action, ...$params): bool;

    /**
     * @template T of \Tempest\Router\HttpMiddleware
     * @param class-string<T> $middlewareClass
     */
    public function addMiddleware(string $middlewareClass): void;
}
