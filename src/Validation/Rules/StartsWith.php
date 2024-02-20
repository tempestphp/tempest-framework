<?php

declare(strict_types=1);

namespace Tempest\Validation\Rules;

use Attribute;
use Tempest\Interface\Rule;

#[Attribute]
final readonly class StartsWith implements Rule
{
    public function __construct(
        private string $needle,
    ) {
    }

    public function isValid(mixed $value): bool
    {
        if (str_starts_with($value, $this->needle)) {
            return true;
        }

        return false;
    }

    public function message(): string
    {
        return "Value should start with {$this->needle}";
    }
}
