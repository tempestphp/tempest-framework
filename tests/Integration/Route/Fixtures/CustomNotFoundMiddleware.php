<?php

namespace Tests\Tempest\Integration\Route\Fixtures;

use Tempest\Core\Priority;
use Tempest\Http\Request;
use Tempest\Http\Response;
use Tempest\Router\HttpMiddleware;
use Tempest\Router\HttpMiddlewareCallable;

#[Priority(Priority::EXCEPTION_HANDLING)]
final class CustomNotFoundMiddleware implements HttpMiddleware
{
    public function __invoke(Request $request, HttpMiddlewareCallable $next): Response
    {
        $response = $next($request);

        $response->addHeader('x-not-found', 'indeed');

        return $response;
    }
}
