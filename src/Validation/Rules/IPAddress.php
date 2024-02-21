<?php

declare(strict_types=1);

namespace Tempest\Validation\Rules;

use Attribute;
use Tempest\Interface\Rule;

#[Attribute]
final readonly class IPAddress implements Rule
{
    public function isValid(mixed $value): bool
    {
        return boolval(filter_var($value, FILTER_VALIDATE_IP));
    }

    public function message(): string
    {
        return 'Value should be a valid IP Address';
    }
}
