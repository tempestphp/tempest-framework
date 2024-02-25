<?php

declare(strict_types=1);

namespace Tempest\Validation\Rules;

use Attribute;
use Tempest\Validation\Rule;

#[Attribute]
final readonly class Timestamp implements Rule
{
    public function __construct()
    {
    }

    public function isValid(mixed $value): bool
    {
        if (! is_int($value) || empty($value)) {
            return false;
        }

        return (bool)date('Y-m-d H:i:s', $value);
    }

    public function message(): string
    {
        return "Value should be a valid timestamp.";
    }
}
