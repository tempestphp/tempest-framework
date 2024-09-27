<?php

namespace Tempest\Auth;

use Tempest\Validation\Rule;

final readonly class UnknownUserRule implements Rule
{
    public function isValid(mixed $value): bool
    {
        return false;
    }

    public function message(): string|array
    {
        return 'No valid user found for these credentials.';
    }
}