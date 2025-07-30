<?php

declare(strict_types=1);

namespace Tempest\Validation\Rules;

use Attribute;
use InvalidArgumentException;
use Tempest\Validation\HasTranslationVariables;
use Tempest\Validation\Rule;

#[Attribute]
final readonly class Count implements Rule, HasTranslationVariables
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
        $length = count($value);

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
