<?php

declare(strict_types=1);

namespace Tempest\Router;

use Tempest\Core\Priority;
use Tempest\Cryptography\Encryption\Encrypter;
use Tempest\Http\Cookie\CookieManager;
use Tempest\Http\Request;
use Tempest\Http\Response;

/**
 * Adds the `Set-Cookie` headers to the response based on the cookie manager.
 */
#[Priority(Priority::FRAMEWORK)]
final readonly class SetCookieHeadersMiddleware implements HttpMiddleware
{
    public function __construct(
        private Encrypter $encrypter,
        private CookieManager $cookies,
    ) {}

    public function __invoke(Request $request, HttpMiddlewareCallable $next): Response
    {
        $response = $next($request);

        foreach ($this->cookies->all() as $cookie) {
            $cookieValue = $cookie->value === ''
                ? ''
                : $this->encrypter->encrypt($cookie->value)->serialize();

            $response->addHeader('set-cookie', (string) $cookie->withValue($cookieValue));
        }

        return $response;
    }
}
