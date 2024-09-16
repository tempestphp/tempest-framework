<?php

declare(strict_types=1);

namespace Tempest\Router\Cookie;

use Tempest\Router\HttpMiddleware;
use Tempest\Router\Request;
use Tempest\Router\Response;

final readonly class SetCookieMiddleware implements HttpMiddleware
{
    public function __construct(private CookieManager $cookies)
    {
    }

    public function __invoke(Request $request, callable $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        foreach ($this->cookies->all() as $cookie) {
            $response->addHeader('set-cookie', (string) $cookie);
        }

        return $response;
    }
}
