<?php

declare(strict_types=1);

namespace Tempest\Validation\Rules;

use Attribute;
use DateTimeImmutable;
use DateTimeInterface;
use Tempest\Validation\Rule;
use Throwable;

#[Attribute]
final readonly class AfterDate implements Rule
{
    private DateTimeImmutable $date;

    public function __construct(
        DateTimeInterface|string $date = 'now',
        private bool $inclusive = false,
    ) {
        $this->date = $date instanceof DateTimeInterface
            ? DateTimeImmutable::createFromInterface($date)
            : new DateTimeImmutable($date);
    }

    public function isValid(mixed $value): bool
    {
        /**
         * @todo
         *
         * We should implement a better date handling, either a support date class that can be created
         * from unknown format, or a config to set a default date format.
         */
        try {
            $value = match ($value instanceof DateTimeInterface) {
                true => DateTimeImmutable::createFromInterface($value),
                false => new DateTimeImmutable($value),
            };
        } catch (Throwable) {
            return false;
        }

        if ($this->inclusive) {
            return $this->date->getTimestamp() <= $value->getTimestamp();
        }

        return $this->date->getTimestamp() < $value->getTimestamp();
    }

    public function message(): string
    {
        $message[] = 'Value must be a date after';

        if ($this->inclusive) {
            $message[] = 'or equal to';
        }

        $message[] = $this->date->format('Y-m-d H:i:s');

        return implode(' ', $message);
    }
}
