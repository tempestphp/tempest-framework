<?php

declare(strict_types=1);

namespace Tempest\Validation\Rules;

use Attribute;
use Tempest\Validation\Rule;

#[Attribute]
final readonly class MACAddress implements Rule
{
    public function isValid(mixed $value): bool
    {
        return boolval(filter_var($value, FILTER_VALIDATE_MAC));
    }

    public function message(): string
    {
        return 'Value should be a valid MAC Address';
    }
}
