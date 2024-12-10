<?php

declare(strict_types=1);

namespace Tempest\Console\Stubs;

use Tempest\Router\Response;
use Tempest\Router\Request;
use Tempest\Router\HttpMiddlewareCallable;
use Tempest\Router\HttpMiddleware;

final class HttpMiddlewareStub implements HttpMiddleware
{
    public function __invoke(Request $request, HttpMiddlewareCallable $next): Response
    {
        return $next($request);
    }
}
