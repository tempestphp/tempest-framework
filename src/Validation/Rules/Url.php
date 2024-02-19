<?php

declare(strict_types=1);

namespace Tempest\Validation\Rules;

use Attribute;
use Tempest\Interface\Rule;
use Throwable;

#[Attribute]
final readonly class Url implements Rule
{
    public function isValid(mixed $value): bool
    {
        try {
            return filter_var($value, FILTER_VALIDATE_URL) !== false;
        } catch (Throwable) {
            return false;
        }
    }

    public function message(): string
    {
        return 'Value should be a valid URL.';
    }
}
