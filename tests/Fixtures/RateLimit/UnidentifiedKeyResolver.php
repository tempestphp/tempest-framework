<?php

declare(strict_types=1);

namespace Tests\Tempest\Fixtures\RateLimit;

use Tempest\Http\Request;
use Tempest\RateLimit\Http\RateLimitKeyResolver;

/**
 * Never identifies a client, as though every request arrived without an address.
 */
final readonly class UnidentifiedKeyResolver implements RateLimitKeyResolver
{
    public function resolve(Request $request): ?string
    {
        return null;
    }
}
