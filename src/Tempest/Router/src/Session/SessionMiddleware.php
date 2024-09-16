<?php

declare(strict_types=1);

namespace Tempest\Router\Session;

use Tempest\Router\HttpMiddleware;
use Tempest\Router\Request;
use Tempest\Router\Response;

final readonly class SessionMiddleware implements HttpMiddleware
{
    public function __construct(private Session $session)
    {
    }

    public function __invoke(Request $request, callable $next): Response
    {
        $response = $next($request);

        $this->session->cleanup();

        return $response;
    }
}
