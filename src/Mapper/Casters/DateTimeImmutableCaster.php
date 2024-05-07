<?php

declare(strict_types=1);

namespace Tempest\Mapper\Casters;

use DateTimeImmutable;
use InvalidArgumentException;
use Tempest\Mapper\Caster;

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

        if (! $date) {
            throw new InvalidArgumentException("Must be a valid date in the format {$this->format}");
        }

        return $date;
    }
}
