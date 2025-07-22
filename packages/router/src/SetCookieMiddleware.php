<?php

declare(strict_types=1);

namespace Tempest\Router;

use Tempest\Core\Priority;
use Tempest\Http\Cookie\CookieManager;
use Tempest\Http\Request;
use Tempest\Http\Response;

/**
 * Adds the `Set-Cookie` headers to the response based on the cookie manager.
 */
#[Priority(Priority::FRAMEWORK)]
final readonly class SetCookieMiddleware implements HttpMiddleware
{
    public function __construct(
        private CookieManager $cookies,
    ) {}

    public function __invoke(Request $request, HttpMiddlewareCallable $next): Response
    {
        $response = $next($request);

        foreach ($this->cookies->all() as $cookie) {
            $response->addHeader('set-cookie', (string) $cookie);
        }

        return $response;
    }
}
