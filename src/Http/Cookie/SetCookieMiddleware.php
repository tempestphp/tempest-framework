<?php

declare(strict_types=1);

namespace Tempest\Http\Cookie;

use DateTimeImmutable;
use Tempest\Http\HttpMiddleware;
use Tempest\Http\Request;
use Tempest\Http\Response;

final readonly class SetCookieMiddleware implements HttpMiddleware
{
    public function __construct(
        private CookieManager $cookieManager,
    ) {
    }

    public function __invoke(Request $request, callable $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        foreach ($this->cookieManager->cookies as $cookie) {
            $response->header('set-cookie', (string) $cookie);
        }

        return $response;
    }
}
