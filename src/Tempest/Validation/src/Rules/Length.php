<?php

declare(strict_types=1);

namespace Tempest\Validation\Rules;

use Attribute;
use InvalidArgumentException;
use Tempest\Validation\Rule;

#[Attribute]
final readonly class Length implements Rule
{
    public function __construct(
        private ?int $min = null,
        private ?int $max = null,
    )
    {
        if ($min === null && $max === null) {
            throw new InvalidArgumentException('At least one of min or max must be provided');
        }
    }

    public function isValid(mixed $value): bool
    {
        if (! is_string($value)) {
            return false;
        }

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

        return "Value should be no more than {$this->max}";
    }
}
