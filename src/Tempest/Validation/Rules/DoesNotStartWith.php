<?php

declare(strict_types=1);

namespace Tempest\Validation\Rules;

use Attribute;
use Tempest\Validation\Rule;

#[Attribute]
final readonly class DoesNotStartWith implements Rule
{
    public function __construct(
        private string $needle
    ) {
    }

    public function isValid(mixed $value): bool
    {
        return ! str_starts_with($value, $this->needle);
    }

    public function message(): string
    {
        return "Value should not start with {$this->needle}";
    }
}
