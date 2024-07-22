<?php

declare(strict_types=1);

namespace Tempest\Validation\Rules;

use Attribute;
use Tempest\Validation\Rule;

#[Attribute]
final readonly class Alpha implements Rule
{
    public function isValid(mixed $value): bool
    {
        return boolval(preg_match('/^[A-Za-z]+$/', $value));
    }

    public function message(): string
    {
        return 'Value should only contain alphabetic characters';
    }
}
