<?php

declare(strict_types=1);

namespace Tempest\Validation\Rules;

use Attribute;
use Tempest\Support\Random;
use Tempest\Validation\Rule;

#[Attribute]
final readonly class Uuid implements Rule
{
    public function isValid(mixed $value): bool
    {
        if (! is_string($value)) {
            return false;
        }

        return Random\is_uuid($value);
    }

    public function message(): string
    {
        return 'Value should contain a valid UUID';
    }
}
