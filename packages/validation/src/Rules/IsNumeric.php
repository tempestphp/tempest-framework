<?php

declare(strict_types=1);

namespace Tempest\Validation\Rules;

use Attribute;
use Tempest\Validation\Rule;

/**
 * Validates that the value is a string containing only numeric characters.
 */
#[Attribute]
final readonly class IsNumeric implements Rule
{
    public function isValid(mixed $value): bool
    {
        if (! is_string($value)) {
            return false;
        }

        return boolval(preg_match('/^[0-9]+$/', $value));
    }
}
