<?php

declare(strict_types=1);

namespace Tempest\Validation\Rules;

use Attribute;
use Tempest\Interface\Rule;

#[Attribute]
final readonly class AlphaNumeric implements Rule
{
    public function isValid(mixed $value): bool
    {
        return boolval(preg_match('/^[A-Za-z0-9]+$/', $value));
    }

    public function message(): string
    {
        return 'Value should only contain alphanumeric characters.';
    }
}
