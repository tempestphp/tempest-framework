<?php

declare(strict_types=1);

namespace Tempest\Router\Responses;

use Tempest\Core\Priority;
use Tempest\Http\Method;
use Tempest\Router\HttpMiddleware;
use Tempest\Router\HttpMiddlewareCallable;
use Tempest\Router\Request;
use Tempest\Router\Response;
use Tempest\Router\Session\Session;

#[Priority(Priority::FRAMEWORK)]
final readonly class SetCurrentUrlMiddleware implements HttpMiddleware
{
    public function __construct(
        private Session $session,
    ) {}

    public function __invoke(Request $request, HttpMiddlewareCallable $next): Response
    {
        if ($request->method === Method::GET) {
            $this->session->setPreviousUrl($request->uri);
        }

        return $next($request);
    }
}
