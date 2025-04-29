<?php

declare(strict_types=1);

namespace Tempest\Validation\Rules;

use Attribute;
use Tempest\Validation\Rule;

#[Attribute]
final readonly class Odd implements Rule
{
    public function isValid(mixed $value): bool
    {
        return is_int($value) && ($value % 2) !== 0;
    }

    public function message(): string
    {
        return 'Value should be an odd number';
    }
}
