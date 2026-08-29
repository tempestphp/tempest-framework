<?php

declare(strict_types=1);

namespace Tempest\RateLimit\Http;

use Tempest\Http\Request;

/**
 * Determines what a request is counted against when a rate limit does not specify a key of its own.
 */
interface RateLimitKeyResolver
{
    /**
     * Returns what the specified request is counted against, or `null` when the client cannot be
     * identified. Unidentified requests share a single counter, and are never left unlimited.
     */
    public function resolve(Request $request): ?string;
}
