<?php

namespace Tempest\Validation\Rules;

use Attribute;
use Tempest\Interface\Rule;

#[Attribute]
final readonly class Time implements Rule
{
    public function __construct(
        private bool $twentyFourHour = false
    ) {}

    public function isValid(mixed $value): bool
    {
        if ($this->twentyFourHour) {
            return preg_match('/^([0-1][0-9]|2[0-3]):?[0-5][0-9]$|^(([0-1]?[0-9]|2[0-3]):[0-5][0-9])$/', $value) === 1;
        }

        return preg_match('/^([0-1]?[0-9]):[0-5][0-9]\s([aApP].[mM].|[aApP][mM])$/', $value) === 1;
    }

    public function message(): string
    {
        if ($this->twentyFourHour) {
            return 'Value should be a valid time in the 24-hour format of hh:mm';
        }

        return 'Value should be a valid time in the format of hh:mm xm';
    }
}