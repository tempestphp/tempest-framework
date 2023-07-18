<?php

declare(strict_types=1);

namespace Tempest\Validation\Rules;

use Attribute;
use Tempest\Interfaces\Rule;

#[Attribute]
final readonly class Count implements Rule
{
    public function __construct(
        private ?int $min = null,
        private ?int $max = null,
    ) {
    }

    public function isValid(mixed $value): bool
    {
        $length = count($value);

        $min = $this->min ?? $length;
        $max = $this->max ?? $length;

        return $length >= $min && $length <= $max;
    }

    public function message(): string
    {
        if ($this->min && $this->max) {
            return "Array should contain between {$this->min} and {$this->max} items";
        }

        if ($this->min) {
            return "Array should contain no less than {$this->min} items";
        }

        if ($this->max) {
            return "Array should contain no more than {$this->max} items";
        }

        return '';
    }
}
