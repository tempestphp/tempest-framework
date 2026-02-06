<?php

declare(strict_types=1);

namespace Tempest\Router;

use Tempest\Http\Request;

/**
 * Interface for custom rate limit identifier resolvers.
 *
 * Implement this interface to provide custom logic for identifying
 * clients for rate limiting purposes.
 *
 * ```php
 * final readonly class ApiKeyResolver implements RateLimitIdentifierResolver
 * {
 *     public function resolve(Request $request): string
 *     {
 *         return $request->headers->get('X-API-Key') ?? 'anonymous';
 *     }
 * }
 *
 * // Usage
 * #[RateLimit(by: ApiKeyResolver::class)]
 * ```
 */
interface RateLimitIdentifierResolver
{
    public function resolve(Request $request): string;
}
