<?php

declare(strict_types=1);

namespace Tempest\Validation\Rules;

use Attribute;
use Tempest\Validation\Rule;

#[Attribute]
final readonly class ShouldBeTrue implements Rule
{
    public function isValid(mixed $value): bool
    {
        return $value === true || $value === 'true' || $value === 1 || $value === '1';
    }

    public function message(): string
    {
        return 'Value should represent a boolean true value.';
    }
}
