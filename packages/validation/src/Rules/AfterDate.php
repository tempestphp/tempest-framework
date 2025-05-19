<?php

declare(strict_types=1);

namespace Tempest\Validation\Rules;

use Attribute;
use DateTimeInterface as NativeDateTimeInterface;
use Tempest\DateTime\DateTime;
use Tempest\DateTime\DateTimeInterface;
use Tempest\Validation\Rule;
use Throwable;

#[Attribute]
final readonly class AfterDate implements Rule
{
    private DateTimeInterface $date;

    public function __construct(
        DateTimeInterface|NativeDateTimeInterface|string $date = 'now',
        private bool $inclusive = false,
    ) {
        $this->date = DateTime::parse($date);
    }

    public function isValid(mixed $value): bool
    {
        try {
            $value = DateTime::parse($value);
        } catch (Throwable) {
            return false;
        }

        if ($this->inclusive) {
            return $value->afterOrAtTheSameTime($this->date);
        }

        return $value->after($this->date);
    }

    public function message(): string
    {
        $message[] = 'Value must be a date after';

        if ($this->inclusive) {
            $message[] = 'or equal to';
        }

        $message[] = $this->date->format();

        return implode(' ', $message);
    }
}
