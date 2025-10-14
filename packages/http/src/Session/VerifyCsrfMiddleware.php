<?php

declare(strict_types=1);

namespace Tempest\Http\Session;

use Tempest\Clock\Clock;
use Tempest\Core\AppConfig;
use Tempest\Core\Priority;
use Tempest\Cryptography\Encryption\Encrypter;
use Tempest\Cryptography\Encryption\Exceptions\EncryptionException;
use Tempest\Http\Cookie\Cookie;
use Tempest\Http\Cookie\CookieManager;
use Tempest\Http\Method;
use Tempest\Http\Request;
use Tempest\Http\Response;
use Tempest\Http\Session\Session;
use Tempest\Router\HttpMiddleware;
use Tempest\Router\HttpMiddlewareCallable;
use Tempest\Support\Json\Exception\JsonCouldNotBeDecoded;
use Tempest\Support\Str;

#[Priority(Priority::FRAMEWORK)]
final readonly class VerifyCsrfMiddleware implements HttpMiddleware
{
    public const string CSRF_COOKIE_KEY = 'XSRF-TOKEN';
    public const string CSRF_HEADER_KEY = 'x-xsrf-token';

    public function __construct(
        private Session $session,
        private AppConfig $appConfig,
        private SessionConfig $sessionConfig,
        private CookieManager $cookies,
        private Clock $clock,
        private Encrypter $encrypter,
    ) {}

    public function __invoke(Request $request, HttpMiddlewareCallable $next): Response
    {
        $this->cookies->add(new Cookie(
            key: self::CSRF_COOKIE_KEY,
            value: $this->session->token,
            expiresAt: $this->clock->now()->plus($this->sessionConfig->expiration),
            path: '/',
            secure: Str\starts_with($this->appConfig->baseUri, 'https'),
        ));

        if ($this->shouldSkipCheck($request)) {
            return $next($request);
        }

        $this->ensureTokenMatches($request);

        return $next($request);
    }

    private function shouldSkipCheck(Request $request): bool
    {
        if (in_array($request->method, [Method::GET, Method::HEAD, Method::OPTIONS], strict: true)) {
            return true;
        }

        if ($this->appConfig->environment->isTesting()) {
            return true;
        }

        return false;
    }

    private function ensureTokenMatches(Request $request): void
    {
        $tokenFromRequest = $request->get(
            key: Session::CSRF_TOKEN_KEY,
        );

        if (! $tokenFromRequest && $request->headers->has(self::CSRF_HEADER_KEY)) {
            try {
                $tokenFromRequest = $this->encrypter->decrypt(
                    urldecode($request->headers->get(self::CSRF_HEADER_KEY)),
                );
            } catch (EncryptionException|JsonCouldNotBeDecoded) {
                throw new CsrfTokenDidNotMatch();
            }
        }

        if (! $tokenFromRequest) {
            throw new CsrfTokenDidNotMatch();
        }

        if (! hash_equals($this->session->token, $tokenFromRequest)) {
            throw new CsrfTokenDidNotMatch();
        }
    }
}
