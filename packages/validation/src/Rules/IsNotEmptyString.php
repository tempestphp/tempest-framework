<?php

declare(strict_types=1);

namespace Tempest\Validation\Rules;

use Attribute;
use Tempest\Validation\Rule;

/**
 * Validates that the value is a non-empty string.
 */
#[Attribute]
final readonly class IsNotEmptyString implements Rule
{
    public function isValid(mixed $value): bool
    {
        return is_string($value) && $value !== '';
    }
}
