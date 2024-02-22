<?php

declare(strict_types=1);

namespace Tempest\Validation\Rules;

use Attribute;
use http\Exception\InvalidArgumentException;
use Tempest\Validation\Rule;

#[Attribute]
final readonly class Password implements Rule
{
    public function __construct(
        private int $min = 8,
        private ?int $max = null,
        private bool $mixedCase = false,
        private bool $numbers = false,
        private bool $letters = false,
        private bool $symbols = false,
    ) {
        if ($this->min < 1) {
            throw new \InvalidArgumentException("Minimum length must be at least 1");
        }

        if ($this->max !== null && $this->max < $this->min) {
            throw new \InvalidArgumentException("Maximum length must be greater than or equal to the minimum length");
        }
    }

    public function isValid(mixed $value): bool
    {
        if (! is_string($value)) {
            throw new InvalidArgumentException("Value must be a string");
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
