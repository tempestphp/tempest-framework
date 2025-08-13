<?php

declare(strict_types=1);

namespace Tempest\Validation\Rules;

use Attribute;
use Tempest\Validation\HasTranslationVariables;
use Tempest\Validation\Rule;

/**
 * Validates that the value is an integer.
 */
#[Attribute]
final readonly class IsInteger implements Rule, HasTranslationVariables
{
    public function __construct(
        private bool $orNull = false,
    ) {}

    public function isValid(mixed $value): bool
    {
        if ($this->orNull && $value === null) {
            return true;
        }

        if ($value === null || $value === false || $value === '' || $value === []) {
            return false;
        }

        // @mago-expect strictness/require-identity-comparison
        return is_int($value) || intval($value) == $value;
    }

    public function getTranslationVariables(): array
    {
        return [
            'or_null' => $this->orNull,
        ];
    }
}
