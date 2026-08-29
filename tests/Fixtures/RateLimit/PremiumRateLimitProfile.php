<?php

declare(strict_types=1);

namespace Tests\Tempest\Fixtures\RateLimit;

use Tempest\Http\Request;
use Tempest\RateLimit\Http\RateLimitProfile;
use Tempest\RateLimit\RateLimit;

final readonly class PremiumRateLimitProfile implements RateLimitProfile
{
    public function resolve(Request $request): array
    {
        if ($request->headers->get('x-api-key') === 'premium') {
            return [];
        }

        return [RateLimit::perMinute(1)];
    }
}
