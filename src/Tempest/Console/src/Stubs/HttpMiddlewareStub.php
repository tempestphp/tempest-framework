<?php

declare(strict_types=1);

namespace Tempest\Console\Stubs;

use Tempest\Core\Priority;
use Tempest\Discovery\DoNotDiscover;
use Tempest\Router\HttpMiddleware;
use Tempest\Router\HttpMiddlewareCallable;
use Tempest\Router\Request;
use Tempest\Router\Response;

#[DoNotDiscover]
#[Priority(Priority::NORMAL)]
final class HttpMiddlewareStub implements HttpMiddleware
{
    public function __invoke(Request $request, HttpMiddlewareCallable $next): Response
    {
        return $next($request);
    }
}
