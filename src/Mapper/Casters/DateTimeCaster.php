<?php

declare(strict_types=1);

namespace Tempest\Mapper\Casters;

use DateTime;
use InvalidArgumentException;
use Tempest\Mapper\Caster;

final readonly class DateTimeCaster implements Caster
{
    public function __construct(
        private string $format = 'Y-m-d H:i:s',
    ) {
    }

    public function cast(mixed $input): DateTime
    {
        $date = DateTime::createFromFormat($this->format, $input);

        if (! $date) {
            throw new InvalidArgumentException("Must be a valid date in the format {$this->format}");
        }

        return $date;
    }
}
