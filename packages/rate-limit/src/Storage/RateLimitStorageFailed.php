<?php

declare(strict_types=1);

namespace Tempest\RateLimit\Storage;

use Exception;
use Tempest\RateLimit\RateLimitException;

final class RateLimitStorageFailed extends Exception implements RateLimitException
{
    public function __construct(
        public readonly string $key,
    ) {
        parent::__construct(sprintf('Redis did not report a window for `%s` after recording an attempt against it.', $key));
    }
}
