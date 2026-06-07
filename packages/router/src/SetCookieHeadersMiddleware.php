<?php

declare(strict_types=1);

namespace Tempest\Router;

use Tempest\Cryptography\Encryption\Encrypter;
use Tempest\Http\Cookie\CookieConfig;
use Tempest\Http\Cookie\CookieManager;
use Tempest\Http\Request;
use Tempest\Http\Response;
use Tempest\Support\Priority;

/**
 * Adds the `Set-Cookie` headers to the response based on the cookie manager.
 */
#[Priority(Priority::FRAMEWORK)]
final readonly class SetCookieHeadersMiddleware implements HttpMiddleware
{
    public function __construct(
        private Encrypter $encrypter,
        private CookieManager $cookies,
        private CookieConfig $cookieConfig,
    ) {}

    public function __invoke(Request $request, HttpMiddlewareCallable $next): Response
    {
        $response = $next($request);

        foreach ($this->cookies->all() as $cookie) {
            $cookieValue = match (true) {
                $cookie->value === '' => '',
                in_array($cookie->key, $this->cookieConfig->plaintextCookies, strict: true) => $cookie->value,
                default => $this->encrypter->encrypt($cookie->value)->serialize(),
            };

            $response->addHeader('set-cookie', (string) $cookie->withValue($cookieValue));
        }

        return $response;
    }
}
