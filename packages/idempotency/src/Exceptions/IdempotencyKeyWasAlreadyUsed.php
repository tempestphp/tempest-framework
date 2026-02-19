<?php

declare(strict_types=1);

namespace Tempest\Idempotency\Exceptions;

use RuntimeException;

final class IdempotencyKeyWasAlreadyUsed extends RuntimeException
{
    public static function forScope(string $scope, string $key): self
    {
        return new self(sprintf('The idempotency key `%s` has already been used with a different payload in `%s`.', $key, $scope));
    }
}
