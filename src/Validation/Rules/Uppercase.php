<?php

declare(strict_types=1);

namespace Tempest\Validation\Rules;

use Attribute;
use Tempest\Interface\Rule;

#[Attribute]
class Uppercase implements Rule
{
    public function isValid(mixed $value): bool
    {
        return $value === mb_strtoupper($value);
    }

    public function message(): string
    {
        return 'Value should be an uppercase string.';
    }
}
