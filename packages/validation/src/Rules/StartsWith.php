<?php

declare(strict_types=1);

namespace Tempest\Validation\Rules;

use Attribute;
use Tempest\Validation\HasTranslationVariables;
use Tempest\Validation\Rule;

#[Attribute]
final readonly class StartsWith implements Rule, HasTranslationVariables
{
    public function __construct(
        private string $needle,
    ) {}

    public function isValid(mixed $value): bool
    {
        return str_starts_with($value, $this->needle);
    }

    public function getTranslationVariables(): array
    {
        return [
            'needle' => $this->needle,
        ];
    }
}
