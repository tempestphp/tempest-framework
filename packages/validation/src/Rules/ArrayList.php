<?php

declare(strict_types=1);

namespace Tempest\Validation\Rules;

use Attribute;
use Tempest\Validation\Rule;

#[Attribute]
final readonly class ArrayList implements Rule
{
    public function isValid(mixed $value): bool
    {
        return is_array($value) && array_is_list($value);
    }

    public function message(): string
    {
        return 'Value must be a list';
    }
}
