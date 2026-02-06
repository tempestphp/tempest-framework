<?php

declare(strict_types=1);

namespace Tempest\Router;

use Tempest\Discovery\SkipDiscovery;
use Tempest\Http\Request;
use Tempest\Http\Response;
use Tempest\Http\Responses\Forbidden;

/**
 * Middleware that validates the signature of a signed URL.
 *
 * Returns 403 Forbidden if:
 * - The signature is missing
 * - The signature is invalid (tampered URL)
 * - The signature has expired (for temporary signed URLs)
 *
 * This middleware uses #[SkipDiscovery] to prevent auto-registration as a global middleware.
 * It should only run on routes that have the #[ValidSignature] attribute.
 */
#[SkipDiscovery]
final readonly class ValidSignatureMiddleware implements HttpMiddleware
{
    public function __construct(
        private UriGenerator $uriGenerator,
    ) {}

    public function __invoke(Request $request, HttpMiddlewareCallable $next): Response
    {
        if (! $this->uriGenerator->hasValidSignature($request)) {
            return new Forbidden();
        }

        return $next($request);
    }
}
