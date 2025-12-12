<?php

declare(strict_types=1);

namespace Tempest\Validation\Rules;

use Attribute;
use Tempest\Validation\HasTranslationVariables;
use Tempest\Validation\Rule;

/**
 * Validates that the value is between a minimum and maximum number (inclusive).
 */
#[Attribute]
final readonly class IsBetween implements Rule, HasTranslationVariables
{
    public function __construct(
        private int $min,
        private int $max,
    ) {}

    public function isValid(mixed $value): bool
    {
        if (! is_numeric($value)) {
            return false;
        }

        return $value >= $this->min && $value <= $this->max;
    }

    public function getTranslationVariables(): array
    {
        return [
            'min' => $this->min,
            'max' => $this->max,
        ];
    }
}
