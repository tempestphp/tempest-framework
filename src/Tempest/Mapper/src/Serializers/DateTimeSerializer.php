<?php

namespace Tempest\Mapper\Serializers;

use DateTime;
use DateTimeInterface;
use Tempest\Mapper\Casters\DateTimeCaster;
use Tempest\Mapper\Exceptions\CannotSerializeValue;
use Tempest\Mapper\Serializer;
use Tempest\Reflection\PropertyReflector;
use Tempest\Validation\Rules\DateTimeFormat;

final class DateTimeSerializer implements Serializer
{
    public function __construct(
        private string $format = DATE_ATOM,
    ) {
    }

    public static function fromProperty(PropertyReflector $property): self
    {
        $format = $property->getAttribute(DateTimeFormat::class)->format ?? DATE_ATOM;

        return new self($format);
    }

    public function serialize(mixed $input): string
    {
        if (! ($input instanceof DateTimeInterface)) {
            throw new CannotSerializeValue(DateTimeInterface::class);
        }

        return $input->format($this->format);
    }
}
