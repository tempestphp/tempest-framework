<?php

declare(strict_types=1);

namespace Tempest\Validation\Rules;

use Attribute;
use Stringable;
use Tempest\Validation\HasTranslationVariables;
use Tempest\Validation\Rule;

#[Attribute]
final readonly class IsString implements Rule, HasTranslationVariables
{
    public function __construct(
        private bool $orNull = false,
    ) {}

    public function isValid(mixed $value): bool
    {
        if ($this->orNull && $value === null) {
            return true;
        }

        if ($value instanceof Stringable) {
            return true;
        }

        return is_string($value);
    }

    public function getTranslationVariables(): array
    {
        return [
            'or_null' => $this->orNull,
        ];
    }
}
