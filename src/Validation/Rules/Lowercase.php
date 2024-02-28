<?php

declare(strict_types=1);

namespace Tempest\Validation\Rules;

use Attribute;
use Tempest\Validation\Rule;

#[Attribute]
final readonly class Lowercase implements Rule
{
    public function isValid(mixed $value): bool
    {
        return $value === mb_strtolower($value);
    }

    public function message(): string
    {
        return 'Value should be a lowercase string';
    }
}
