<?php

declare(strict_types=1);

namespace Tempest\Validation\Rules;

use Attribute;
use Stringable;
use Tempest\Validation\Rule;

#[Attribute]
final readonly class IsString implements Rule
{
    public function __construct(
        private bool $orNull = false,
    ) {}

    public function isValid(mixed $value): bool
    {
        if ($this->orNull && $value === null) {
            return true;
        }

        if ($value instanceof Stringable) {
            return true;
        }

        return is_string($value);
    }

    public function message(): string
    {
        return 'Value should be a string';
    }
}
