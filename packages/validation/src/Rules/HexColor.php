<?php

declare(strict_types=1);

namespace Tempest\Validation\Rules;

use Attribute;
use Tempest\Validation\Rule;

#[Attribute]
final readonly class HexColor implements Rule
{
    public function __construct(
        private bool $orNull = false,
    ) {}

    public function isValid(mixed $value): bool
    {
        if ($this->orNull && $value === null) {
            return true;
        }

        if (! is_string($value)) {
            return false;
        }

        return preg_match('/^#([0-9A-Fa-f]{3}|[0-9A-Fa-f]{4}|[0-9A-Fa-f]{6}|[0-9A-Fa-f]{8})$/i', $value) === 1;
    }

    public function message(): string
    {
        return 'Value should be a valid hexadecimal color.';
    }
}
