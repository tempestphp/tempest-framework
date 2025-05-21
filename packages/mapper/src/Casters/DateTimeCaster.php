<?php

declare(strict_types=1);

namespace Tempest\Mapper\Casters;

use DateTimeInterface as NativeDateTimeInterface;
use Tempest\DateTime\DateTime;
use Tempest\DateTime\DateTimeInterface;
use Tempest\DateTime\FormatPattern;
use Tempest\Mapper\Caster;
use Tempest\Reflection\PropertyReflector;
use Tempest\Validation\Rules\DateTimeFormat;

final readonly class DateTimeCaster implements Caster
{
    public function __construct(
        private FormatPattern|string $format = FormatPattern::ISO8601,
    ) {}

    public static function fromProperty(PropertyReflector $property): self
    {
        return new self(
            $property->getAttribute(DateTimeFormat::class)->format ?? FormatPattern::ISO8601,
        );
    }

    public function cast(mixed $input): ?DateTimeInterface
    {
        if (! $input) {
            return null;
        }

        if ($input instanceof DateTimeInterface) {
            return $input;
        }

        try {
            return DateTime::parse($input);
        } catch (\Throwable) {
            return null;
        }
    }
}
