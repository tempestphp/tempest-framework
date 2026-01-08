<?php

declare(strict_types=1);

namespace Tempest\Router;

use Tempest\Core\Priority;
use Tempest\Http\Method;
use Tempest\Http\Request;
use Tempest\Http\Response;
use Tempest\Http\Responses\Forbidden;

/**
 * Protects against cross-site requests using `Sec-Fetch-*` headers. This is a modern equivalent of session token-based cross-site forgery protection.
 *
 * - Safe HTTP methods are always allowed
 * - Requests from same-origin or same-site are allowed
 * - Cross-site requests that aren't navigation are blocked
 * - Requests without Sec-Fetch-Site headers are blocked
 *
 * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Sec-Fetch-Site
 * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Sec-Fetch-Mode
 * @see https://web.dev/articles/fetch-metadata
 */
#[Priority(Priority::FRAMEWORK - 8)]
final readonly class PreventCrossSiteRequestsMiddleware implements HttpMiddleware
{
    private const array SAFE_METHODS = [
        Method::GET,
        Method::HEAD,
        Method::OPTIONS,
        Method::TRACE,
    ];

    public function __invoke(Request $request, HttpMiddlewareCallable $next): Response
    {
        if (! $this->shouldValidate($request)) {
            return $next($request);
        }

        if ($this->isValidRequest($request)) {
            return $next($request);
        }

        return new Forbidden();
    }

    /**
     * Determines if the request should be validated for CSRF.
     */
    private function shouldValidate(Request $request): bool
    {
        if (in_array($request->method, self::SAFE_METHODS, strict: true)) {
            return false;
        }

        return true;
    }

    /**
     * Validates the request using `Sec-Fetch-*` headers.
     */
    private function isValidRequest(Request $request): bool
    {
        $secFetchSite = SecFetchSite::tryFrom($request->headers->get('sec-fetch-site', default: ''));
        $secFetchMode = SecFetchMode::tryFrom($request->headers->get('sec-fetch-mode', default: ''));

        // prevent the request if there is no `sec-fetch-site` header
        if ($secFetchSite === null) {
            return false;
        }

        // allow cross-site only on navigation requests
        if ($secFetchSite === SecFetchSite::CROSS_SITE && $secFetchMode === SecFetchMode::NAVIGATE) {
            return true;
        }

        // same origin, same site and user-originated requests are always allowed
        if ($secFetchSite !== SecFetchSite::CROSS_SITE) {
            return true;
        }

        return false;
    }
}
