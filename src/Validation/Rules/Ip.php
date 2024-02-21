<?php

declare(strict_types=1);

namespace Tempest\Validation\Rules;

use Attribute;
use Tempest\Interface\Rule;

#[Attribute]
final readonly class Ip implements Rule
{
    public function isValid(mixed $value): bool
    {
        return (
            (filter_var($value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) ? true : false) ||
            (filter_var($value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) ? true : false)
        );
    }

    public function message(): string
    {
        return 'Value should be a valid IP.';
    }
}
