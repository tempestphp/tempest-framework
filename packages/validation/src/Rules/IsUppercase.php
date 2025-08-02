<?php

declare(strict_types=1);

namespace Tempest\Validation\Rules;

use Attribute;
use Tempest\Validation\Rule;

/**
 * Validates that the value is an uppercase string.
 */
#[Attribute]
final readonly class IsUppercase implements Rule
{
    public function isValid(mixed $value): bool
    {
        return $value === mb_strtoupper($value);
    }
}
