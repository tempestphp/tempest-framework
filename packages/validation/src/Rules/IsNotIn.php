<?php

declare(strict_types=1);

namespace Tempest\Validation\Rules;

use Attribute;
use Tempest\Validation\HasTranslationVariables;
use Tempest\Validation\Rule;

/**
 * Validates that the value is not contained within a specified list of values.
 */
#[Attribute]
final readonly class IsNotIn implements Rule, HasTranslationVariables
{
    public function __construct(
        /** @var array<string|int> */
        private array $values,
    ) {}

    public function isValid(mixed $value): bool
    {
        return new IsIn($this->values, not: true)->isValid($value);
    }

    public function getTranslationVariables(): array
    {
        return [
            'values' => $this->values,
        ];
    }
}
