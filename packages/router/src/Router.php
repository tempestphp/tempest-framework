<?php

declare(strict_types=1);

namespace Tempest\Router;

use Psr\Http\Message\ServerRequestInterface as PsrRequest;
use Tempest\Http\Request;
use Tempest\Http\Response;

interface Router
{
    public function dispatch(Request|PsrRequest $request): Response;

    /**
     * Creates a valid URI to the given `$action`.
     *
     * `$action` is one of :
     * - Controller FQCN and its method as a tuple
     * - Invokable controller FQCN
     * - URI string starting with `/`
     *
     * @param array{class-string, string}|class-string|string $action
     */
    public function toUri(array|string $action, ...$params): string;

    /**
     * Checks if the URI to the given `$action` would match the current route.
     *
     * `$action` is one of :
     * - Controller FQCN and its method as a tuple
     * - Invokable controller FQCN
     * - URI string starting with `/`
     *
     * @param array{class-string, string}|class-string|string $action
     */
    public function isCurrentUri(array|string $action, ...$params): bool;
}
