<?php

declare(strict_types=1);

namespace Tempest\Validation\Rules;

use Attribute;
use Tempest\Validation\HasTranslationVariables;
use Tempest\Validation\Rule;

#[Attribute]
final readonly class MultipleOf implements Rule, HasTranslationVariables
{
    public function __construct(
        public int $divisor,
    ) {}

    public function isValid(mixed $value): bool
    {
        return is_int($value) && ($value % $this->divisor) === 0;
    }

    public function getTranslationVariables(): array
    {
        return [
            'divisor' => $this->divisor,
        ];
    }
}
