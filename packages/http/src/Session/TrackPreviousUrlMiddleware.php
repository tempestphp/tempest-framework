<?php

declare(strict_types=1);

namespace Tempest\Http\Session;

use Tempest\Http\Request;
use Tempest\Http\Response;
use Tempest\Router\HttpMiddleware;
use Tempest\Router\HttpMiddlewareCallable;

final readonly class TrackPreviousUrlMiddleware implements HttpMiddleware
{
    public function __construct(
        private PreviousUrl $previousUrl,
    ) {}

    public function __invoke(Request $request, HttpMiddlewareCallable $next): Response
    {
        $this->previousUrl->track($request);

        return $next($request);
    }
}
