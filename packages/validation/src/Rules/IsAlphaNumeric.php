<?php

declare(strict_types=1);

namespace Tempest\Validation\Rules;

use Attribute;
use Tempest\Validation\Rule;

/**
 * Validates that the value is a string containing only alphabetic and numeric characters.
 */
#[Attribute]
final readonly class IsAlphaNumeric implements Rule
{
    public function isValid(mixed $value): bool
    {
        if (! is_string($value)) {
            return false;
        }

        return boolval(preg_match('/^[A-Za-z0-9]+$/', $value));
    }
}
