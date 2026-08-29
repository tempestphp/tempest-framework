<?php

declare(strict_types=1);

namespace Tempest\RateLimit;

final class RateLimitHasNoKey extends RateLimitException
{
    public static function forLimit(RateLimit $limit): self
    {
        return new self(sprintf(
            'A rate limit of %d attempts was used without a key. Scope it with `withKey()` or `scopedTo()`, '
            . 'otherwise it would share a counter with every other keyless limit.',
            $limit->attempts,
        ));
    }
}
