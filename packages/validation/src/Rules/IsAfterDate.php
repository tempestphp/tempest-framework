<?php

declare(strict_types=1);

namespace Tempest\Validation\Rules;

use Attribute;
use DateTimeInterface as NativeDateTimeInterface;
use Tempest\DateTime\DateTime;
use Tempest\DateTime\DateTimeInterface;
use Tempest\Validation\HasTranslationVariables;
use Tempest\Validation\Rule;
use Throwable;

/**
 * Validates that the value is a date that comes after a specified date.
 */
#[Attribute]
final readonly class IsAfterDate implements Rule, HasTranslationVariables
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

    public function getTranslationVariables(): array
    {
        return [
            'inclusive' => $this->inclusive,
            'date' => $this->date,
        ];
    }
}
