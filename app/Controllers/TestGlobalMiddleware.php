<?php

declare(strict_types=1);

namespace App\Controllers;

use Tempest\Http\HttpMiddleware;
use Tempest\Http\Request;
use Tempest\Http\Response;

final readonly class TestGlobalMiddleware implements HttpMiddleware
{
    public function __invoke(Request $request, callable $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        $response->addHeader('global-middleware', 'yes');

        return $response;
    }
}
