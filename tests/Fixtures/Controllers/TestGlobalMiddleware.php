<?php

declare(strict_types=1);

namespace Tests\Tempest\Fixtures\Controllers;

use Tempest\Discovery\SkipDiscovery;
use Tempest\Http\Request;
use Tempest\Http\Response;
use Tempest\Router\HttpMiddleware;
use Tempest\Router\HttpMiddlewareCallable;

#[SkipDiscovery]
final readonly class TestGlobalMiddleware implements HttpMiddleware
{
    public function __invoke(Request $request, HttpMiddlewareCallable $next): Response
    {
        $response = $next($request);

        $response->addHeader('global-middleware', 'yes');

        return $response;
    }
}
