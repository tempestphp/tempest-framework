<?php

declare(strict_types=1);

namespace Tempest\Validation\Rules;

use Attribute;
use Tempest\Validation\HasTranslationVariables;
use Tempest\Validation\Rule;

/**
 * Validates that the value ends with a specified string.
 */
#[Attribute]
final readonly class EndsWith implements Rule, HasTranslationVariables
{
    public function __construct(
        private string $needle,
    ) {}

    public function isValid(mixed $value): bool
    {
        if (! is_string($value)) {
            return false;
        }

        return str_ends_with($value, $this->needle);
    }

    public function getTranslationVariables(): array
    {
        return [
            'needle' => $this->needle,
        ];
    }
}
