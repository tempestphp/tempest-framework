<?php

declare(strict_types=1);

namespace Tempest\Validation\Rules;

use Attribute;
use Tempest\Validation\Rule;

#[Attribute]
final readonly class Between implements Rule
{
    public function __construct(
        private int $min,
        private int $max,
    ) {
    }

    public function isValid(mixed $value): bool
    {
        if ($value >= $this->min && $value <= $this->max) {
            return true;
        }

        return false;
    }

    public function message(): string
    {
        return "Value should be between {$this->min} and {$this->max}";
    }
}
