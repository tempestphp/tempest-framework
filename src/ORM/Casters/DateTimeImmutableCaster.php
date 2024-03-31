<?php

declare(strict_types=1);

namespace Tempest\ORM\Casters;

use DateTimeImmutable;
use DateTimeInterface;
use InvalidArgumentException;
use ReflectionParameter;
use ReflectionProperty;
use Tempest\ORM\DynamicCaster;

final readonly class DateTimeImmutableCaster implements DynamicCaster
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

    public function shouldCast(ReflectionParameter|ReflectionProperty $property, mixed $value): bool
    {
        return in_array($property->getType()?->getName(), [
            DateTimeImmutable::class,
            DateTimeInterface::class,
        ], true);
    }
}
