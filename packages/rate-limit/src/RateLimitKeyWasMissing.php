<?php

declare(strict_types=1);

namespace Tempest\RateLimit;

use Exception;

final class RateLimitKeyWasMissing extends Exception implements RateLimitException
{
    public function __construct(
        public readonly RateLimit $limit,
    ) {
        parent::__construct(sprintf(
            'A rate limit of %d attempts was used without a key. Scope it with `withKey()` or `scopedTo()`, '
            . 'otherwise it would share a counter with every other keyless limit.',
            $limit->attempts,
        ));
    }
}
