<?php

declare(strict_types=1);

namespace Tempest\Validation\Rules;

use Attribute;
use Tempest\Validation\Rule;

/**
 * Validates that the value is a truthy value (true, 'true', 1, or '1').
 */
#[Attribute]
final readonly class IsTruthy implements Rule
{
    public function isValid(mixed $value): bool
    {
        return in_array($value, [true, 'true', 1, '1', 'on', 'yes', 'enabled'], strict: true);
    }
}
