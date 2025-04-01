<?php

declare(strict_types=1);

namespace Tempest\Validation\Rules;

use Attribute;
use DateTime;
use DateTimeInterface;
use Tempest\Validation\Rule;

#[Attribute]
final readonly class DateTimeFormat implements Rule
{
    public const string FORMAT = 'Y-m-d H:i:s';

    public function __construct(
        public string $format = self::FORMAT,
    ) {}

    public function isValid(mixed $value): bool
    {
        $value = match ($value instanceof DateTimeInterface) {
            true => $value->format($this->format),
            default => $value,
        };

        if (! is_string($value) || ! $value) {
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
