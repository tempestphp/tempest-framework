<?php

declare(strict_types=1);

namespace Tempest\Console\Stubs;

use Tempest\Core\Priority;
use Tempest\Discovery\SkipDiscovery;
use Tempest\Http\Request;
use Tempest\Http\Response;
use Tempest\Router\HttpMiddleware;
use Tempest\Router\HttpMiddlewareCallable;

#[SkipDiscovery]
#[Priority(Priority::NORMAL)]
final class HttpMiddlewareStub implements HttpMiddleware
{
    public function __invoke(Request $request, HttpMiddlewareCallable $next): Response
    {
        return $next($request);
    }
}
