<?php

declare(strict_types=1);

namespace Tests\Tempest\Fixtures\Controllers;

use Tempest\Router\HttpMiddleware;
use Tempest\Router\HttpMiddlewareCallable;
use Tempest\Router\Request;
use Tempest\Router\Response;

final readonly class TestMiddleware implements HttpMiddleware
{
    public function __construct(
        private MiddlewareDependency $middlewareDependency,
    ) {}

    public function __invoke(Request $request, HttpMiddlewareCallable $next): Response
    {
        $response = $next($request);

        $response->addHeader('middleware', $this->middlewareDependency->value);

        return $response;
    }
}
