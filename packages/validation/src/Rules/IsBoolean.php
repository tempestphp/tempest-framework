<?php

declare(strict_types=1);

namespace Tempest\Validation\Rules;

use Attribute;
use Tempest\Validation\HasTranslationVariables;
use Tempest\Validation\Rule;

/**
 * Validates that the value is a boolean or boolean-like value (truthy/falsy).
 */
#[Attribute]
final readonly class IsBoolean implements Rule, HasTranslationVariables
{
    public function __construct(
        private bool $orNull = false,
    ) {}

    public function isValid(mixed $value): bool
    {
        if ($this->orNull && $value === null) {
            return true;
        }

        return new IsTruthy()->isValid($value) || new IsFalsy()->isValid($value);
    }

    public function getTranslationVariables(): array
    {
        return [
            'or_null' => $this->orNull,
        ];
    }
}
