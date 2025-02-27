<?php

declare(strict_types=1);

namespace Tempest\Mapper\Casters;

use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use Tempest\Mapper\Caster;
use Tempest\Mapper\Exceptions\CannotSerializeValue;
use Tempest\Reflection\PropertyReflector;
use Tempest\Validation\Rules\DateTimeFormat;

final readonly class DateTimeCaster implements Caster
{
    public function __construct(
        private string $format = DATE_ATOM,
        private bool $immutable = true,
    ) {
    }

    public static function fromProperty(PropertyReflector $property): DateTimeCaster
    {
        $format = $property->getAttribute(DateTimeFormat::class)->format ?? DATE_ATOM;

        return match ($property->getType()->getName()) {
            DateTime::class => new DateTimeCaster($format, immutable: false),
            default => new DateTimeCaster($format, immutable: true),
        };
    }

    public function cast(mixed $input): DateTimeInterface
    {
        if ($input instanceof DateTimeInterface) {
            return $input;
        }

        $class = $this->immutable ? DateTimeImmutable::class : DateTime::class;

        $date = $class::createFromFormat($this->format, $input);

        if (! $date) {
            $date = new $class($input);
        }

        return $date;
    }

    public function serialize(mixed $input): string
    {
        if (! $input instanceof DateTimeInterface) {
            throw new CannotSerializeValue(DateTimeInterface::class);
        }

        return $input->format($this->format);
    }
}
