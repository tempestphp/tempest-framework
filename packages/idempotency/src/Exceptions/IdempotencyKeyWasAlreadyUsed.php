<?php

declare(strict_types=1);

namespace Tempest\Idempotency\Exceptions;

final class IdempotencyKeyWasAlreadyUsed extends IdempotencyException
{
    public static function forScope(string $scope, string $key): self
    {
        return new self(sprintf('The idempotency key `%s` has already been used with a different payload in `%s`.', $key, $scope));
    }
}
