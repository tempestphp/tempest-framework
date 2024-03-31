<?php

declare(strict_types=1);

namespace Tempest\Validation\Rules;

use Attribute;
use Tempest\Validation\Rule;

#[Attribute]
final readonly class StartsWith implements Rule
{
    public function __construct(
        private string $needle,
    ) {
    }

    public function isValid(mixed $value): bool
    {
        return str_starts_with((string) $value, $this->needle);
    }

    public function message(): string
    {
        return "Value should start with {$this->needle}";
    }
}
