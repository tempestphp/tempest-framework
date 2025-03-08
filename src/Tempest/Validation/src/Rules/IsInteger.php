<?php

declare(strict_types=1);

namespace Tempest\Validation\Rules;

use Attribute;
use Tempest\Validation\Rule;

#[Attribute]
final readonly class IsInteger implements Rule
{
    public function isValid(mixed $value): bool
    {
        return is_int($value);
    }

    public function message(): string
    {
        return 'Value should be an integer';
    }
}
