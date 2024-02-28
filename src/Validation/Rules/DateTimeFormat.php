<?php

declare(strict_types=1);

namespace Tempest\Validation\Rules;

use Attribute;
use DateTime;
use Tempest\Validation\Rule;

#[Attribute]
final readonly class DateTimeFormat implements Rule
{
    public function __construct(private string $format = 'Y-m-d')
    {
    }

    public function isValid(mixed $value): bool
    {
        if (! is_string($value) || empty($value)) {
            return false;
        }

        $date = DateTime::createFromFormat($this->format, $value);

        return $date && $date->format($this->format) === $value;
    }

    public function message(): string
    {
        return "Value should be a valid datetime in the format {$this->format}";
    }
}
