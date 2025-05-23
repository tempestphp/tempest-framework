<?php

declare(strict_types=1);

namespace Tempest\Mapper\Serializers;

use DateTimeInterface;
use Tempest\Mapper\Exceptions\CannotSerializeValue;
use Tempest\Mapper\Serializer;
use Tempest\Reflection\PropertyReflector;
use Tempest\Validation\Rules\DateTimeFormat;

final readonly class NativeDateTimeSerializer implements Serializer
{
    public function __construct(
        private string $format = 'Y-m-d H:i:s',
    ) {}

    public static function fromProperty(PropertyReflector $property): self
    {
        $format = $property->getAttribute(DateTimeFormat::class)->format ?? 'Y-m-d H:i:s';

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
