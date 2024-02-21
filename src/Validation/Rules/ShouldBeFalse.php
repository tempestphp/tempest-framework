<?php

declare(strict_types=1);

namespace Tempest\Validation\Rules;

use Attribute;
use Tempest\Validation\Rule;

#[Attribute]
final readonly class ShouldBeFalse implements Rule
{
    public function isValid(mixed $value): bool
    {
        return $value === false || $value === 'false' || $value === 0 || $value === '0';
    }

    public function message(): string
    {
        return 'Value should represent a boolean false value.';
    }
}
