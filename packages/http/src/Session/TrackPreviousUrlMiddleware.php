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
        private PreviousUrl $previousUrlTracker,
    ) {}

    public function __invoke(Request $request, HttpMiddlewareCallable $next): Response
    {
        $this->previousUrlTracker->track($request);

        return $next($request);
    }
}
