<?php

declare(strict_types=1);

namespace Tempest\Console\Stubs;

use Tempest\Http\HttpMiddleware;
use Tempest\Http\HttpMiddlewareCallable;
use Tempest\Http\Request;
use Tempest\Http\Response;

final class HttpMiddlewareStub implements HttpMiddleware
{
    public function __invoke(Request $request, HttpMiddlewareCallable $next): Response
    {
        $response = $next($request);

        return $response;
    }
}
