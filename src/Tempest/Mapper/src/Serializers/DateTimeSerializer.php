<?php

namespace Tempest\Mapper\Serializers;

use DateTimeInterface;
use Tempest\Mapper\Serializer;
use Tempest\Reflection\PropertyReflector;
use Tempest\Validation\Rules\DateTimeFormat;
use Throwable;

final class DateTimeSerializer implements Serializer
{
    public function __construct(
        private string $format = 'c',
    ) {}

    public static function fromProperty(PropertyReflector $property): self
    {
        $format = $property->getAttribute(DateTimeFormat::class)->format ?? 'c';

        return new self($format);
    }

    public function serialize(mixed $input): string|null
    {
        if ($input instanceof DateTimeInterface) {
            return $input->format($this->format);
        }

        try {
            return (string)$input;
        } catch (Throwable) {
            return null;
        }
    }
}