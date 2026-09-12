<?php

declare(strict_types=1);

namespace Tempest\RateLimit\Http;

use Tempest\Http\Request;
use Tempest\RateLimit\RateLimit;

/**
 * Describes the rate limits that apply to a request. Referenced from {@see Throttle}, a profile
 * is used when the limits depend on the request itself.
 */
interface RateLimitProfile
{
    /**
     * Returns the rate limits that apply to the specified request. An empty array leaves the request
     * unlimited.
     *
     * Limits without a key are scoped to the route and to the client resolved by
     * {@see RateLimitKeyResolver}. A limit with a key is counted under that key as written, with no
     * scoping of its own, so give it one that identifies what it counts, such as `login:{$email}`.
     * That counter can be inspected or cleared through {@see \Tempest\RateLimit\RateLimiter}.
     *
     * @return RateLimit[]
     */
    public function resolve(Request $request): array;
}
