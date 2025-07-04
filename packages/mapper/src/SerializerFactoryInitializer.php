<?php

declare(strict_types=1);

namespace Tempest\Mapper;

use BackedEnum;
use DateTime as NativeDateTime;
use DateTimeImmutable as NativeDateTimeImmutable;
use DateTimeInterface as NativeDateTimeInterface;
use JsonSerializable;
use Serializable;
use Stringable;
use Tempest\Container\Container;
use Tempest\Container\Initializer;
use Tempest\Container\Singleton;
use Tempest\DateTime\DateTime;
use Tempest\DateTime\DateTimeInterface;
use Tempest\Mapper\Serializers\ArrayOfObjectsSerializer;
use Tempest\Mapper\Serializers\ArrayToJsonSerializer;
use Tempest\Mapper\Serializers\BooleanSerializer;
use Tempest\Mapper\Serializers\DateTimeSerializer;
use Tempest\Mapper\Serializers\EnumSerializer;
use Tempest\Mapper\Serializers\FloatSerializer;
use Tempest\Mapper\Serializers\IntegerSerializer;
use Tempest\Mapper\Serializers\NativeDateTimeSerializer;
use Tempest\Mapper\Serializers\SerializableSerializer;
use Tempest\Mapper\Serializers\StringSerializer;
use Tempest\Reflection\PropertyReflector;
use Tempest\DateTime\DateTime as TempestDateTime;

final class SerializerFactoryInitializer implements Initializer
{
    #[Singleton]
    public function initialize(Container $container): SerializerFactory
    {
        return new SerializerFactory()
            ->addSerializer('bool', BooleanSerializer::class)
            ->addSerializer('float', FloatSerializer::class)
            ->addSerializer('int', IntegerSerializer::class)
            ->addSerializer('string', StringSerializer::class)
            ->addSerializer('array', ArrayToJsonSerializer::class)
            ->addSerializer(DateTimeInterface::class, DateTimeSerializer::fromProperty(...))
            ->addSerializer(NativeDateTimeImmutable::class, NativeDateTimeSerializer::fromProperty(...))
            ->addSerializer(NativeDateTimeInterface::class, NativeDateTimeSerializer::fromProperty(...))
            ->addSerializer(NativeDateTime::class, NativeDateTimeSerializer::fromProperty(...))
            ->addSerializer(Stringable::class, StringSerializer::class)
            ->addSerializer(Serializable::class, SerializableSerializer::class)
            ->addSerializer(JsonSerializable::class, SerializableSerializer::class)
            ->addSerializer(BackedEnum::class, EnumSerializer::class)
            ->addSerializer(DateTime::class, DateTimeSerializer::fromProperty(...))
            ->addSerializer(TempestDateTime::class, DateTimeSerializer::fromProperty(...))
            ->addSerializer(fn (PropertyReflector $property) => $property->getIterableType() !== null, ArrayOfObjectsSerializer::class);
    }
}
