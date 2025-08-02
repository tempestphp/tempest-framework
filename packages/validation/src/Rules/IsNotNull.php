<?php

declare(strict_types=1);

namespace Tempest\Validation\Rules;

use Attribute;
use Tempest\Validation\Rule;

/**
 * Validates that the value is not null.
 */
#[Attribute]
final readonly class IsNotNull implements Rule
{
    public function isValid(mixed $value): bool
    {
        return $value !== null;
    }
}
