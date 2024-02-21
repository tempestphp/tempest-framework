<?php

declare(strict_types=1);

namespace Tempest\Validation\Rules;

use Attribute;
use Tempest\Validation\Rule;

#[Attribute]
final readonly class Length implements Rule
{
    public function __construct(
        private ?int $min = null,
        private ?int $max = null,
    ) {
    }

    public function isValid(mixed $value): bool
    {
        $length = strlen($value);

        $min = $this->min ?? $length;
        $max = $this->max ?? $length;

        return $length >= $min && $length <= $max;
    }

    public function message(): string
    {
        if ($this->min && $this->max) {
            return "Value should be between {$this->min} and {$this->max}";
        }

        if ($this->min) {
            return "Value should be no less than {$this->min}";
        }

        if ($this->max) {
            return "Value should be no more than {$this->max}";
        }

        return '';
    }
}
