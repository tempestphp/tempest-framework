<?php

declare(strict_types=1);

namespace Tempest\Validation\Rules;

use Attribute;
use Tempest\Support\Random;
use Tempest\Validation\Rule;

/**
 * Validates that the value is a valid ULID (universally unique lexicographically sortable identifier).
 */
#[Attribute]
final readonly class IsUlid implements Rule
{
    public function isValid(mixed $value): bool
    {
        if (! is_string($value)) {
            return false;
        }

        return Random\is_ulid($value);
    }
}
