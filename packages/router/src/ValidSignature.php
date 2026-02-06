<?php

declare(strict_types=1);

namespace Tempest\Router;

use Attribute;

/**
 * Validates that the request has a valid signature.
 *
 * This attribute should be used on routes that require a signed URL.
 * If the signature is invalid or missing, a 403 Forbidden response is returned.
 * If the signature has expired (for temporary signed URLs), a 403 Forbidden response is also returned.
 *
 * Usage:
 * ```php
 * #[Get('/verify-email')]
 * #[ValidSignature]
 * public function verifyEmail(string $token): Response
 * {
 *     // This code only executes if the signature is valid
 * }
 * ```
 *
 * @see UriGenerator::createSignedUri()
 * @see UriGenerator::createTemporarySignedUri()
 */
#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_CLASS)]
final class ValidSignature implements RouteDecorator
{
    public function decorate(Route $route): Route
    {
        // ValidSignatureMiddleware intentionally doesn't implement HttpMiddleware to prevent
        // auto-discovery as a global middleware. It follows the same callable signature
        // and is invoked via HandleRouteSpecificMiddleware.
        $route->middleware = [
            ...$route->middleware,
            ValidSignatureMiddleware::class,
        ];

        return $route;
    }
}
