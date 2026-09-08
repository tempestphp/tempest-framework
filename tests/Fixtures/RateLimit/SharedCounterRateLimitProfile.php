<?php

declare(strict_types=1);

namespace Tests\Tempest\Fixtures\RateLimit;

use Tempest\Http\Request;
use Tempest\RateLimit\Http\RateLimitProfile;
use Tempest\RateLimit\RateLimit;

/**
 * Names its own counter. The key is used as written, so the routes using this profile share an
 * allowance that can be addressed through the limiter.
 */
final readonly class SharedCounterRateLimitProfile implements RateLimitProfile
{
    public function resolve(Request $request): array
    {
        return [RateLimit::perMinute(2)->withKey('shared-counter')];
    }
}
