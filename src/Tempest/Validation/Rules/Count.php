<?php

declare(strict_types=1);

namespace Tempest\Validation\Rules;

use Attribute;
use InvalidArgumentException;
use Tempest\Validation\Rule;

#[Attribute]
final readonly class Count implements Rule
{
    public function __construct(
        private ?int $min = null,
        private ?int $max = null,
    ) {
        if($min === null && $max === null) {
            throw new InvalidArgumentException("At least one of min or max must be provided");
        }
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

        return "Array should contain no more than {$this->max} items";
    }
}
