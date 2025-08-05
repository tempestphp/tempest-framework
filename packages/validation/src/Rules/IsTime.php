<?php

declare(strict_types=1);

namespace Tempest\Validation\Rules;

use Attribute;
use Tempest\Validation\HasTranslationVariables;
use Tempest\Validation\Rule;

/**
 * Validates that the value is a valid time format.
 */
#[Attribute]
final readonly class IsTime implements Rule, HasTranslationVariables
{
    public function __construct(
        private bool $twentyFourHour = true,
    ) {}

    public function isValid(mixed $value): bool
    {
        if (! is_string($value)) {
            return false;
        }

        if ($this->twentyFourHour) {
            return preg_match('/^([0-1][0-9]|2[0-3]):?[0-5][0-9]$|^(([0-1]?[0-9]|2[0-3]):[0-5][0-9])$/', $value) === 1;
        }

        return preg_match('/^([0-1]?[0-9]):[0-5][0-9]\s([aApP].[mM].|[aApP][mM])$/', $value) === 1;
    }

    public function getTranslationVariables(): array
    {
        return [
            'twenty_four_hour' => $this->twentyFourHour,
        ];
    }
}
