<?php

declare(strict_types=1);

namespace Tempest\ORM\Casters;

use DateTimeImmutable;
use Tempest\ORM\Caster;
use InvalidArgumentException;

final readonly class DateTimeImmutableCaster implements Caster
{
    private string $format;

    public function __construct(
        ?string $format,
    ) {
        $this->format = $format ?? 'Y-m-d H:i:s';
    }

    public function cast(mixed $input): DateTimeImmutable
    {
        $date = DateTimeImmutable::createFromFormat($this->format, $input);

        /**
         * Applies to this and DateTimeCaster.
         *
         * Handles the case when the date is not valid.
         * This should be preventable from user land by adding a date time format validation rule.
         *
         * This is not ideal, as preferably, this would be handled by the format validation class
         * I'm not sure how you want to handle this, so in order to not blow the scope up, I'll leave it here for now.
         *
         * One of the ideas would be a deferred cast to the date AFTER the validation has passed,
         * this way we could avoid throwing exceptions and handle the error in a more controlled manner.
         *
         *
         */
        if (! $date) {
            throw new InvalidArgumentException("Must be a valid date in the format {$this->format}");
        }

        return $date;
    }
}
