<?php

declare(strict_types=1);

namespace Tempest\Validation\Rules;

use Attribute;
use Tempest\Validation\Rule;

#[Attribute]
final readonly class In implements Rule
{
    public function __construct(
        /** @var array<string|int> */
        private array $values,
        private bool $not = false,
    ) {
    }

    public function isValid(mixed $value): bool
    {
        $isPartOf = in_array($value, $this->values, true);

        return $this->not ? ! $isPartOf : $isPartOf;
    }

    public function message(): string
    {
        return 'Value should be one of: ' . implode(', ', $this->values);
    }
}
