<?php

declare(strict_types=1);

namespace Tempest\Validation\Rules;

use Attribute;
use Tempest\Support\Random;
use Tempest\Validation\Rule;

/**
 * Validates that the value is a valid UUID (universally unique identifier).
 */
#[Attribute]
final readonly class IsUuid implements Rule
{
    public function isValid(mixed $value): bool
    {
        if (! is_string($value)) {
            return false;
        }

        return Random\is_uuid($value);
    }
}
