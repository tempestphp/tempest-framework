<?php

declare(strict_types=1);

namespace Tempest\Validation\Rules;

use Attribute;
use Tempest\Validation\Rule;

/**
 * Validates that the value is an even integer.
 */
#[Attribute]
final readonly class IsEvenNumber implements Rule
{
    public function isValid(mixed $value): bool
    {
        return is_int($value) && ($value % 2) === 0;
    }
}
