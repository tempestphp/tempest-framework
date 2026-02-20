<?php

declare(strict_types=1);

namespace Tempest\Idempotency\Exceptions;

use RuntimeException;

final class UnsupportedIdempotencyPlatform extends RuntimeException
{
    public static function forWindows(): self
    {
        return new self('Idempotency is not supported on Windows.');
    }
}
