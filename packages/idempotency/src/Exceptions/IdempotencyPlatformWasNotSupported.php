<?php

declare(strict_types=1);

namespace Tempest\Idempotency\Exceptions;

final class IdempotencyPlatformWasNotSupported extends IdempotencyException
{
    public static function forWindows(): self
    {
        return new self('Idempotency is not supported on Windows.');
    }
}
