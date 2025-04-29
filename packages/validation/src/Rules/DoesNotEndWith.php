<?php

declare(strict_types=1);

namespace Tempest\Validation\Rules;

use Attribute;
use Tempest\Validation\Rule;

#[Attribute]
final readonly class DoesNotEndWith implements Rule
{
    public function __construct(
        private string $needle,
    ) {}

    public function isValid(mixed $value): bool
    {
        return ! str_ends_with($value, $this->needle);
    }

    public function message(): string
    {
        return "Value should not end with {$this->needle}";
    }
}
