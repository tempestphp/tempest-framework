<?php

declare(strict_types=1);

namespace Tempest\Idempotency\Exceptions;

use Tempest\Http\Method;
use Tempest\Idempotency\SupportedMethod;

final class IdempotencyMethodWasNotSupported extends IdempotencyException
{
    public static function forMethod(Method $method): self
    {
        return new self(sprintf('Idempotency is only supported for %s methods, `%s` given.', SupportedMethod::allowed(), $method->value));
    }
}
