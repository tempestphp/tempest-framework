<?php

declare(strict_types=1);

namespace Tempest\Validation\Rules;

use Attribute;
use Tempest\Validation\Rule;

#[Attribute]
final readonly class NotNull implements Rule
{
    public function isValid(mixed $value): bool
    {
        return $value !== null;
    }

    public function message(): string
    {
        return 'Value must not be null';
    }
}
