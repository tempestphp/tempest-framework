<?php

declare(strict_types=1);

namespace Tempest\Validation\Rules;

use Attribute;
use Tempest\Validation\HasTranslationVariables;
use Tempest\Validation\Rule;

/**
 * Validates that the value is divisible by a specified number.
 */
#[Attribute]
final readonly class IsDivisibleBy implements Rule, HasTranslationVariables
{
    public function __construct(
        public int $divisor,
    ) {}

    public function isValid(mixed $value): bool
    {
        if (! is_numeric($value) || $value === 0) {
            return false;
        }

        return new IsMultipleOf($this->divisor)->isValid($value);
    }

    public function getTranslationVariables(): array
    {
        return [
            'divisor' => $this->divisor,
        ];
    }
}
