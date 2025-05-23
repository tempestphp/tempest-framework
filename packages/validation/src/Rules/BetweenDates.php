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
final readonly class BetweenDates implements Rule
{
    private DateTimeInterface $first;
    private DateTimeInterface $second;

    public function __construct(
        DateTimeInterface|NativeDateTimeInterface|string $first,
        DateTimeInterface|NativeDateTimeInterface|string $second,
        private bool $inclusive = false,
    ) {
        $this->first = DateTime::parse($first);
        $this->second = DateTime::parse($second);
    }

    public function isValid(mixed $value): bool
    {
        try {
            $value = DateTime::parse($value);
        } catch (Throwable) {
            return false;
        }

        if ($this->inclusive) {
            return $value->betweenTimeInclusive($this->first, $this->second);
        }

        return $value->betweenTimeExclusive($this->first, $this->second);
    }

    public function message(): string
    {
        return "Value must be a date between {$this->first} and {$this->second}";
    }
}
