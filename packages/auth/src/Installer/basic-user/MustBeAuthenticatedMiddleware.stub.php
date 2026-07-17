<?php

namespace Tempest\Auth\Installer;

use Tempest\Auth\Authentication\Authenticator;
use Tempest\Discovery\SkipDiscovery;
use Tempest\Http\Request;
use Tempest\Http\Response;
use Tempest\Http\Responses\Forbidden;
use Tempest\Router\HttpMiddleware;
use Tempest\Router\HttpMiddlewareCallable;

#[SkipDiscovery]
final readonly class MustBeAuthenticatedMiddleware implements HttpMiddleware
{
    public function __construct(
        private Authenticator $authenticator,
    ) {}

    public function __invoke(Request $request, HttpMiddlewareCallable $next): Response
    {
        if (! $this->authenticator->current()) {
            return new Forbidden();
        }

        return $next($request);
    }
}