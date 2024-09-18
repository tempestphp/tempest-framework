<?php

declare(strict_types=1);

namespace Tempest\Routing;

use Psr\Http\Message\ServerRequestInterface as PsrRequest;
use Tempest\Http\Request;
use Tempest\Http\Response;

interface Router
{
    public function dispatch(Request|PsrRequest $request): Response;

    public function toUri(array|string $action, ...$params): string;

    /**
     * @template T of \Tempest\Http\HttpMiddleware
     * @param class-string<T> $middlewareClass
     */
    public function addMiddleware(string $middlewareClass): void;
}
