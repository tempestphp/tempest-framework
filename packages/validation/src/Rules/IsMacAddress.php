<?php

declare(strict_types=1);

namespace Tempest\Validation\Rules;

use Attribute;
use Tempest\Validation\Rule;

/**
 * Validates that the value is a valid MAC address.
 */
#[Attribute]
final readonly class IsMacAddress implements Rule
{
    public function isValid(mixed $value): bool
    {
        return boolval(filter_var($value, FILTER_VALIDATE_MAC));
    }
}
