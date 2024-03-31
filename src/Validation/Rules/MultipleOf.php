<?php

declare(strict_types=1);

namespace Tempest\Validation\Rules;

use Attribute;
use Tempest\Validation\Rule;

#[Attribute]
final readonly class MultipleOf implements Rule
{
    public function __construct(
        public int $divisor,
    )
    {

    }

    public function isValid(mixed $value): bool
    {
        return is_int($value) && ($value % $this->divisor === 0);
    }

    public function message(): string
    {
        return 'Value should be a multiple of ' . $this->divisor;
    }
}
