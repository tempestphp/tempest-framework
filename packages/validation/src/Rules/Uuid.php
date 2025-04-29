<?php

declare(strict_types=1);

namespace Tempest\Validation\Rules;

use Attribute;
use Tempest\Validation\Rule;

#[Attribute]
final readonly class Uuid implements Rule
{
    public function isValid(mixed $value): bool
    {
        if (! is_string($value)) {
            return false;
        }

        return boolval(preg_match('/^[a-f\d]{8}(-[a-f\d]{4}){4}[a-f\d]{8}$/i', $value));
    }

    public function message(): string
    {
        return 'Value should contain a valid UUID';
    }
}
