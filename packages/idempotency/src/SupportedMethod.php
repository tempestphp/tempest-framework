<?php

declare(strict_types=1);

namespace Tempest\Idempotency;

use Tempest\Http\Method;

enum SupportedMethod: string
{
    case POST = 'POST';
    case PATCH = 'PATCH';

    public static function isSupported(Method $method): bool
    {
        return self::tryFrom($method->value) !== null;
    }

    public static function allowed(): string
    {
        return implode(', ', array_column(self::cases(), 'value'));
    }
}
