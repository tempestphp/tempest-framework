<?php

declare(strict_types=1);

namespace Tempest\Mapper\Serializers;

use DateTimeInterface;
use Tempest\Mapper\Exceptions\ValueCouldNotBeSerialized;
use Tempest\Mapper\Serializer;
use Tempest\Reflection\ClassReflector;
use Tempest\Reflection\PropertyReflector;
use Tempest\Reflection\TypeReflector;
use Tempest\Validation\Rules\HasDateTimeFormat;

final readonly class NativeDateTimeSerializer implements Serializer
{
    public function __construct(
        private string $format = 'Y-m-d H:i:s',
    ) {}

    public static function fromReflector(PropertyReflector|TypeReflector $property): self
    {
        if ($property instanceof PropertyReflector) {
            $format = $property->getAttribute(HasDateTimeFormat::class)?->format ?? 'Y-m-d H:i:s';
        } else {
            $format = 'Y-m-d H:i:s';
        }

        return new self($format);
    }

    public function serialize(mixed $input): string
    {
        if (! $input instanceof DateTimeInterface) {
            throw new ValueCouldNotBeSerialized(DateTimeInterface::class);
        }

        return $input->format($this->format);
    }
}
