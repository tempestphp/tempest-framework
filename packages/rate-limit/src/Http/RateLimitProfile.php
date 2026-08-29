<?php

declare(strict_types=1);

namespace Tempest\RateLimit\Http;

use Tempest\Http\Request;
use Tempest\RateLimit\RateLimit;

/**
 * Describes the rate limits that apply to a request. Referenced from {@see ThrottleWith}, a profile
 * is used instead of {@see Throttle} when the limits depend on the request itself.
 */
interface RateLimitProfile
{
    /**
     * Returns the rate limits that apply to the specified request. An empty array leaves the request
     * unlimited.
     *
     * Limits without a key are scoped to the route and to the client resolved by
     * {@see RateLimitKeyResolver}. Limits with a key are scoped to the client alone. Only give a key
     * to a limit whose counter should be shared beyond this route.
     *
     * @return RateLimit[]
     */
    public function resolve(Request $request): array;
}
