<?php

declare(strict_types=1);

namespace Tempest\Validation\Rules;

use Attribute;
use Tempest\Validation\Rule;

#[Attribute]
final readonly class RegEx implements Rule
{
    public function __construct(private string $pattern)
    {
    }

    public function isValid(mixed $value): bool
    {
        return preg_match($this->pattern, $value) === 1;
    }

    public function message(): string
    {
        return "The value must match the regular expression pattern: {$this->pattern}";
    }
}
