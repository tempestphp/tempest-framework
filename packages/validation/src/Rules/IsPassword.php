<?php

declare(strict_types=1);

namespace Tempest\Validation\Rules;

use Attribute;
use Tempest\Validation\HasTranslationVariables;
use Tempest\Validation\Rule;

/**
 * Validates that the value meets password strength requirements.
 */
#[Attribute]
final readonly class IsPassword implements Rule, HasTranslationVariables
{
    private int $min;

    public function __construct(
        int $min = 12,
        private bool $mixedCase = false,
        private bool $numbers = false,
        private bool $letters = false,
        private bool $symbols = false,
    ) {
        $this->min = max(1, $min);
    }

    public function isValid(mixed $value): bool
    {
        if (! is_string($value)) {
            return false;
        }

        if (strlen($value) < $this->min) {
            return false;
        }

        if ($this->mixedCase && ! preg_match('/(\p{Ll}+.*\p{Lu})|(\p{Lu}+.*\p{Ll})/u', $value)) {
            return false;
        }

        if ($this->numbers && ! preg_match('/\p{N}/u', $value)) {
            return false;
        }

        if ($this->letters && ! preg_match('/\p{L}/u', $value)) {
            return false;
        }

        if ($this->symbols && ! preg_match('/\p{Z}|\p{S}|\p{P}/u', $value)) {
            return false;
        }

        return true;
    }

    public function getTranslationVariables(): array
    {
        return [
            'min' => $this->min,
            'mixed_case' => $this->mixedCase,
            'numbers' => $this->numbers,
            'letters' => $this->letters,
            'symbols' => $this->symbols,
        ];
    }
}
