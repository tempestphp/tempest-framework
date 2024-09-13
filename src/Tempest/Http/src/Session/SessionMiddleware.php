<?php

declare(strict_types=1);

namespace Tempest\Http\Session;

use Tempest\Http\HttpMiddleware;
use Tempest\Http\Request;
use Tempest\Http\Response;

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
