<?php

declare(strict_types=1);

namespace Tempest\ORM\Casters;

use DateTime;
use InvalidArgumentException;
use ReflectionParameter;
use ReflectionProperty;
use Tempest\ORM\DynamicCaster;

final readonly class DateTimeCaster implements DynamicCaster
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

    public function shouldCast(ReflectionParameter|ReflectionProperty $property, mixed $value): bool
    {
        return in_array($property->getType()?->getName(), [
            DateTime::class,
        ], true);
    }
}
