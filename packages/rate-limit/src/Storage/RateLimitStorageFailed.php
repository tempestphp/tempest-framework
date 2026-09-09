<?php

declare(strict_types=1);

namespace Tempest\RateLimit\Storage;

use Exception;
use Tempest\RateLimit\RateLimitException;
use Throwable;

/**
 * Thrown when the window for a key could not be read or recorded. The attempt it belongs to has no
 * outcome: the counter behind it is unknown, not within its limit.
 */
final class RateLimitStorageFailed extends Exception implements RateLimitException
{
    public function __construct(
        public readonly string $key,
        string $reason = 'the storage did not report a window after recording an attempt against it',
        ?Throwable $previous = null,
    ) {
        parent::__construct(sprintf('The rate limit for `%s` could not be recorded: %s.', $key, $reason), previous: $previous);
    }
}
