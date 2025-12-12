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
        if (! is_numeric($value)) {
            return false;
        }

        $intValue = (int) $value;

        return ($intValue % $this->divisor) === 0;
    }

    public function getTranslationVariables(): array
    {
        return [
            'divisor' => $this->divisor,
        ];
    }
}
