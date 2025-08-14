<?php

namespace Tests\Tempest\Integration\Http\Fixtures;

use Tempest\Core\Priority;
use Tempest\Http\Request;
use Tempest\Http\Response;
use Tempest\Http\Responses\Ok;
use Tempest\Router\HttpMiddleware;
use Tempest\Router\HttpMiddlewareCallable;

#[Priority(Priority::EXCEPTION_HANDLING)]
final class CustomErrorMiddleware implements HttpMiddleware
{
    public function __invoke(Request $request, HttpMiddlewareCallable $next): Response
    {
        $response = $next($request);

        if ($response->status->isSuccessful()) {
            return $response;
        }

        return new Ok('Something went wrong!');
    }
}