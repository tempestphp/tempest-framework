<?php

declare(strict_types=1);

namespace Tempest\Validation\Rules;

use Attribute;
use Tempest\Validation\Rule;

#[Attribute]
final readonly class IsInteger implements Rule
{
    public function __construct(
        private bool $orNull = false,
    ) {}

    public function isValid(mixed $value): bool
    {
        if ($this->orNull && $value === null) {
            return true;
        }

        return is_int($value);
    }

    public function message(): string
    {
        return 'Value should be an integer';
    }
}
