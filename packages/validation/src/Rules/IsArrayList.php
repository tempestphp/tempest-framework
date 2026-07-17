<?php

declare(strict_types=1);

namespace Tempest\Validation\Rules;

use Attribute;
use Tempest\Support\Arr\ImmutableArray;
use Tempest\Support\Arr\MutableArray;
use Tempest\Validation\Rule;

/**
 * Validates that the value is an array with consecutive integer keys starting from 0.
 */
#[Attribute]
final readonly class IsArrayList implements Rule
{
    public function isValid(mixed $value): bool
    {
        if ($value instanceof ImmutableArray || $value instanceof MutableArray) {
            return $value->isList();
        }

        return is_array($value) && array_is_list($value);
    }
}
