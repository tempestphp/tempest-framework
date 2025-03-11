<?php

declare(strict_types=1);

namespace Tempest\Validation\Rules;

use Attribute;
use Tempest\Validation\Rule;

#[Attribute]
final readonly class IsBoolean implements Rule
{
    public function __construct(
        private bool $orNull = false,
    ) {
    }

    public function isValid(mixed $value): bool
    {
        if ($this->orNull && $value === null) {
            return true;
        }

        return $value === false || $value === 'false' || $value === 0 || $value === '0' || $value === true || $value === 'true' || $value === 1 || $value === '1';
    }

    public function message(): string
    {
        return 'Value should represent a boolean value';
    }
}
