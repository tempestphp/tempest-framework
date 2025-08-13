<?php

declare(strict_types=1);

namespace Tempest\Validation\Rules;

use Attribute;
use Tempest\Validation\Rule;

/**
 * Validates that the value is a falsy value (false, 'false', 0, or '0').
 */
#[Attribute]
final readonly class IsFalsy implements Rule
{
    public function isValid(mixed $value): bool
    {
        return in_array($value, [false, 'false', 0, '0', 'off', 'no', 'disabled'], strict: true);
    }
}
