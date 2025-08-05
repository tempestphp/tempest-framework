<?php

declare(strict_types=1);

namespace Tempest\Validation\Rules;

use Attribute;
use Tempest\Validation\Rule;

/**
 * Validates that the value is a valid Unix timestamp.
 */
#[Attribute]
final readonly class IsUnixTimestamp implements Rule
{
    public function isValid(mixed $value): bool
    {
        if (! filter_var($value, FILTER_VALIDATE_INT)) {
            return false;
        }

        return (bool) date('Y-m-d H:i:s', $value);
    }
}
