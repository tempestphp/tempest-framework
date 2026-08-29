<?php

declare(strict_types=1);

namespace Tempest\RateLimit;

final class RateLimitWasExceeded extends RateLimitException
{
    public function __construct(
        public readonly RateLimitResult $result,
    ) {
        parent::__construct(sprintf(
            'The rate limit of %d attempts for `%s` was exceeded, retry in %d second(s).',
            $result->limit,
            $result->key,
            $result->retryAfterInSeconds,
        ));
    }
}
