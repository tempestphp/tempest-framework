<?php

declare(strict_types=1);

namespace Tempest\Validation\Rules;

use Attribute;
use InvalidArgumentException;
use Tempest\Validation\HasTranslationVariables;
use Tempest\Validation\Rule;

/**
 * Validates that the value has a specific character length or falls within a length range.
 */
#[Attribute]
final readonly class HasLength implements Rule, HasTranslationVariables
{
    public function __construct(
        private ?int $min = null,
        private ?int $max = null,
    ) {
        if ($min === null && $max === null) {
            throw new InvalidArgumentException('At least one of min or max must be provided');
        }
    }

    public function isValid(mixed $value): bool
    {
        if (! is_string($value)) {
            return false;
        }

        $length = mb_strlen($value);

        $min = $this->min ?? $length;
        $max = $this->max ?? $length;

        return $length >= $min && $length <= $max;
    }

    public function getTranslationVariables(): array
    {
        return [
            'min' => $this->min,
            'max' => $this->max,
        ];
    }
}
