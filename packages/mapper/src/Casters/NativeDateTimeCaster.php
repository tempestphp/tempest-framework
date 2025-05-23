<?php

declare(strict_types=1);

namespace Tempest\Mapper\Casters;

use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use Tempest\Mapper\Caster;
use Tempest\Reflection\PropertyReflector;
use Tempest\Validation\Rules\DateTimeFormat;

final readonly class NativeDateTimeCaster implements Caster
{
    public function __construct(
        private string $format = 'Y-m-d H:i:s',
        private bool $immutable = true,
    ) {}

    public static function fromProperty(PropertyReflector $property): NativeDateTimeCaster
    {
        $format = $property->getAttribute(DateTimeFormat::class)->format ?? 'Y-m-d H:i:s';

        return match ($property->getType()->getName()) {
            DateTime::class => new NativeDateTimeCaster($format, immutable: false),
            default => new NativeDateTimeCaster($format, immutable: true),
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
