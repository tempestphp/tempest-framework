<?php

declare(strict_types=1);

namespace Tests\Tempest\Fixtures\RateLimit;

use Tempest\Http\Request;
use Tempest\RateLimit\Http\RateLimitProfile;
use Tempest\RateLimit\RateLimit;

/**
 * Returns several limits at once, none of which has a key of its own.
 */
final readonly class TieredRateLimitProfile implements RateLimitProfile
{
    public function resolve(Request $request): array
    {
        return [
            RateLimit::perMinute(3),
            RateLimit::perDay(1),
        ];
    }
}
