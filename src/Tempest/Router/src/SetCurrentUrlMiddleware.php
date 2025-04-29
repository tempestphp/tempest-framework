<?php

declare(strict_types=1);

namespace Tempest\Router;

use Tempest\Core\Priority;
use Tempest\Http\Method;
use Tempest\Http\Request;
use Tempest\Http\Response;
use Tempest\Http\Session\Session;

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
