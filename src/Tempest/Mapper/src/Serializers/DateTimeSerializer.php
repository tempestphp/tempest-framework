<?php

namespace Tempest\Mapper\Serializers;

use DateTimeInterface;
use Tempest\Mapper\Exceptions\CannotSerializeValue;
use Tempest\Mapper\Serializer;
use Tempest\Reflection\PropertyReflector;
use Tempest\Validation\Rules\DateTimeFormat;

final readonly class DateTimeSerializer implements Serializer
{
    public function __construct(
        private string $format = DateTimeFormat::FORMAT,
    ) {
    }

    public static function fromProperty(PropertyReflector $property): self
    {
        $format = $property->getAttribute(DateTimeFormat::class)->format ?? DateTimeFormat::FORMAT;

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
