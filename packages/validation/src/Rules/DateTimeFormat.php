<?php

declare(strict_types=1);

namespace Tempest\Validation\Rules;

use Attribute;
use DateTimeImmutable as NativeDateTimeImmutable;
use Tempest\DateTime\DateTime;
use Tempest\DateTime\FormatPattern;
use Tempest\Validation\Rule;

#[Attribute]
final readonly class DateTimeFormat implements Rule
{
    /**
     * @param string|FormatPattern $format An ICU or legacy datetime format ({@see https://unicode-org.github.io/icu/userguide/format_parse/datetime/#datetime-format-syntax}, {@see https://www.php.net/manual/en/datetime.format.php}).
     */
    public function __construct(
        public string|FormatPattern $format,
    ) {}

    public function isValid(mixed $value): bool
    {
        if (! is_string($value)) {
            return false;
        }

        return $this->validateIcuFormat($value) || $this->validateNativeFormat($value);
    }

    private function validateIcuFormat(string $value): bool
    {
        try {
            return $value === DateTime::fromPattern($value, $this->format)->format($this->format);
        } catch (\Throwable) {
            return false;
        }
    }

    private function validateNativeFormat(string $value): bool
    {
        if (! ($date = NativeDateTimeImmutable::createFromFormat($this->format, $value))) {
            return false;
        }

        return $date->format($this->format) === $value;
    }

    public function message(): string
    {
        return "Value should be a valid datetime in the format {$this->format}";
    }
}
