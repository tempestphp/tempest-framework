<?php

declare(strict_types=1);

namespace Tempest\Router;

use Tempest\Http\Method;
use Tempest\Http\Request;
use Tempest\Http\Response;
use Tempest\Http\Responses\Forbidden;
use Tempest\Support\Priority;

/**
 * Protects against cross-site requests using `Sec-Fetch-*` headers. This is a modern equivalent of session token-based cross-site forgery protection.
 *
 * - Safe HTTP methods are always allowed
 * - Requests from same-origin or same-site are allowed
 * - Cross-site requests using unsafe methods are blocked
 * - Requests without Sec-Fetch-Site headers are blocked
 *
 * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Sec-Fetch-Site
 * @see https://web.dev/articles/fetch-metadata
 */
#[Priority(Priority::FRAMEWORK - 8)]
final readonly class PreventCrossSiteRequestsMiddleware implements HttpMiddleware
{
    private const array SAFE_METHODS = [
        Method::GET,
        Method::HEAD,
        Method::QUERY,
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
        return ! in_array($request->method, self::SAFE_METHODS, strict: true);
    }

    /**
     * Validates the request using `Sec-Fetch-*` headers.
     */
    private function isValidRequest(Request $request): bool
    {
        $secFetchSite = SecFetchSite::tryFrom($request->headers->get('sec-fetch-site', default: ''));

        // prevent the request if there is no `sec-fetch-site` header
        if ($secFetchSite === null) {
            return false;
        }

        // same origin, same site and user-originated requests are always allowed
        return $secFetchSite !== SecFetchSite::CROSS_SITE;
    }
}
