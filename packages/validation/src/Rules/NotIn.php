<?php

declare(strict_types=1);

namespace Tempest\Validation\Rules;

use Attribute;
use Tempest\Validation\HasTranslationVariables;
use Tempest\Validation\Rule;

#[Attribute]
final readonly class NotIn implements Rule, HasTranslationVariables
{
    public function __construct(
        /** @var array<string|int> */
        private array $values,
    ) {}

    public function isValid(mixed $value): bool
    {
        return new In($this->values, not: true)->isValid($value);
    }

    public function getTranslationVariables(): array
    {
        return [
            'values' => $this->values,
        ];
    }
}
