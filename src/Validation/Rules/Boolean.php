<?php

declare(strict_types=1);

namespace Tempest\Validation\Rules;

use Attribute;
use Tempest\Interface\Rule;

#[Attribute]
final readonly class Boolean implements Rule
{
    public function isValid(mixed $value): bool
    {
        return (
            $value === false || $value === 'false' || $value === 0 || $value === '0' ||
            $value === true || $value === 'true' || $value === 1 || $value === '1'
        );
    }

    public function message(): string
    {
        return 'Value should represent a boolean value';
    }
}
