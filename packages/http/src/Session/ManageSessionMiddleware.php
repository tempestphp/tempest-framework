<?php

namespace Tempest\Http\Session;

use Tempest\Core\Priority;
use Tempest\Http\Request;
use Tempest\Http\Response;
use Tempest\Router\HttpMiddleware;
use Tempest\Router\HttpMiddlewareCallable;

/**
 * This middleware is responsible for creating the session and saving it on response.
 */
#[Priority(Priority::FRAMEWORK - 20)]
final readonly class ManageSessionMiddleware implements HttpMiddleware
{
    public function __construct(
        private SessionManager $sessionManager,
        private Session $session,
    ) {}

    public function __invoke(Request $request, HttpMiddlewareCallable $next): Response
    {
        try {
            return $next($request);
        } finally {
            $this->session->cleanup();
            $this->sessionManager->save($this->session);
            $this->sessionManager->deleteExpiredSessions();
        }
    }
}
