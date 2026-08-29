<?php

declare(strict_types=1);

namespace Tempest\RateLimit\Storage;

use Tempest\RateLimit\RateLimitException;

final class RateLimitStorageFailed extends RateLimitException
{
    public static function redisDidNotReportAWindow(string $key): self
    {
        return new self(sprintf('Redis did not report a window for `%s` after recording an attempt against it.', $key));
    }
}
