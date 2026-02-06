<?php

declare(strict_types=1);

namespace Tempest\Router;

use Tempest\Container\Container;
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
 * Note: This class intentionally does NOT implement HttpMiddleware to prevent
 * it from being auto-discovered as a global middleware. It should only run
 * on routes that have the #[ValidSignature] attribute, via HandleRouteSpecificMiddleware.
 */
final readonly class ValidSignatureMiddleware
{
    public function __construct(
        private Container $container,
    ) {}

    public function __invoke(Request $request, HttpMiddlewareCallable $next): Response
    {
        $uriGenerator = $this->container->get(UriGenerator::class);

        if (! $uriGenerator->hasValidSignature($request)) {
            return new Forbidden();
        }

        return $next($request);
    }
}
