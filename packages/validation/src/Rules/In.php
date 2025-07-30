<?php

declare(strict_types=1);

namespace Tempest\Validation\Rules;

use Attribute;
use Tempest\Validation\HasTranslationVariables;
use Tempest\Validation\Rule;

#[Attribute]
final readonly class In implements Rule, HasTranslationVariables
{
    public function __construct(
        /** @var array<string|int> */
        private array $values,
        private bool $not = false,
    ) {}

    public function isValid(mixed $value): bool
    {
        $isPartOf = in_array($value, $this->values, strict: true);

        return $this->not ? ! $isPartOf : $isPartOf;
    }

    public function getTranslationVariables(): array
    {
        return [
            'values' => $this->values,
            'not' => $this->not,
        ];
    }
}
