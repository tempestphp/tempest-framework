<?php

declare(strict_types=1);

namespace Tempest\Validation\Rules;

use Attribute;
use Tempest\Validation\Rule;

#[Attribute]
final readonly class NotEmpty implements Rule
{
    public function isValid(mixed $value): bool
    {
        return is_string($value) && $value !== '';
    }

    public function message(): string
    {
        return 'Value should be a non-empty string';
    }
}
