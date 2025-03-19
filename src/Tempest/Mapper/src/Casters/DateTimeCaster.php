<?php

declare(strict_types=1);

namespace Tempest\Mapper\Casters;

use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use Tempest\Mapper\Caster;
use Tempest\Reflection\PropertyReflector;
use Tempest\Validation\Rules\DateTimeFormat;

final readonly class DateTimeCaster implements Caster
{
    public function __construct(
        private string $format = DateTimeFormat::FORMAT,
        private bool $immutable = true,
    ) {
    }

    public static function fromProperty(PropertyReflector $property): DateTimeCaster
    {
        $format = $property->getAttribute(DateTimeFormat::class)->format ?? DateTimeFormat::FORMAT;

        return match ($property->getType()->getName()) {
            DateTime::class => new DateTimeCaster($format, immutable: false),
            default => new DateTimeCaster($format, immutable: true),
        };
    }

    public function cast(mixed $input): ?DateTimeInterface
    {
        if (! $input) {
            return null;
        }

        if ($input instanceof DateTimeInterface) {
            return $input;
        }

        $class = $this->immutable ? DateTimeImmutable::class : DateTime::class;

        $date = $class::createFromFormat($this->format, $input);

        if (! $date) {
            return new $class($input);
        }

        return $date;
    }
}
