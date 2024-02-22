<?php

declare(strict_types=1);

namespace Tempest\Validation\Rules;

use Attribute;
use Tempest\Validation\Rule;

#[Attribute]
final readonly class Password implements Rule
{
    private int $min;
    private ?int $max;

    public function __construct(
        int $min = 8,
        ?int $max = null,
        private bool $mixedCase = false,
        private bool $numbers = false,
        private bool $letters = false,
        private bool $symbols = false,
    ) {
        $this->min = max(1, $min);
        $this->max = $max ? max($this->min, $max) : null;
    }

    public function isValid(mixed $value): bool
    {
        if (! is_string($value)) {
            return false;
        }

        if (strlen($value) < $this->min) {
            return false;
        }

        if ($this->max !== null && strlen($value) > $this->max) {
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

    public function message(): string
    {
        return "Value should be a valid password";
    }
}
