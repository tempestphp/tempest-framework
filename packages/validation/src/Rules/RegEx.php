<?php

declare(strict_types=1);

namespace Tempest\Validation\Rules;

use Attribute;
use Tempest\Validation\HasTranslationVariables;
use Tempest\Validation\Rule;

#[Attribute]
final readonly class RegEx implements Rule, HasTranslationVariables
{
    public function __construct(
        private string $pattern,
    ) {}

    public function isValid(mixed $value): bool
    {
        if (! is_string($value)) {
            return false;
        }

        return preg_match($this->pattern, $value) === 1;
    }

    public function getTranslationVariables(): array
    {
        return [
            'pattern' => $this->pattern,
        ];
    }
}
