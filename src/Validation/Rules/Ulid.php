<?php

declare(strict_types=1);

namespace Tempest\Validation\Rules;

use Attribute;
use Tempest\Validation\Rule;

#[Attribute]
final readonly class Ulid implements Rule
{
    public function isValid(mixed $value): bool
    {
        return preg_match('/^[0-9A-HJKMNP-TV-Z]{26}$/', $value) === 1;
    }

    public function message(): string
    {
        return "Value should be a valid ULID";
    }
}
