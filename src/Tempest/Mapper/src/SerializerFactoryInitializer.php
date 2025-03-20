<?php

namespace Tempest\Mapper;

use BackedEnum;
use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use JsonSerializable;
use Serializable;
use Stringable;
use Tempest\Container\Container;
use Tempest\Container\Initializer;
use Tempest\Container\Singleton;
use Tempest\Mapper\Serializers\ArrayOfObjectsSerializer;
use Tempest\Mapper\Serializers\ArrayToJsonSerializer;
use Tempest\Mapper\Serializers\BooleanSerializer;
use Tempest\Mapper\Serializers\DateTimeSerializer;
use Tempest\Mapper\Serializers\EnumSerializer;
use Tempest\Mapper\Serializers\FloatSerializer;
use Tempest\Mapper\Serializers\IntegerSerializer;
use Tempest\Mapper\Serializers\SerializableSerializer;
use Tempest\Mapper\Serializers\StringSerializer;
use Tempest\Reflection\PropertyReflector;

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
            ->addSerializer(DateTimeImmutable::class, DateTimeSerializer::fromProperty(...))
            ->addSerializer(DateTimeInterface::class, DateTimeSerializer::fromProperty(...))
            ->addSerializer(DateTime::class, DateTimeSerializer::fromProperty(...))
            ->addSerializer(Stringable::class, StringSerializer::class)
            ->addSerializer(Serializable::class, SerializableSerializer::class)
            ->addSerializer(JsonSerializable::class, SerializableSerializer::class)
            ->addSerializer(BackedEnum::class, EnumSerializer::class)
            ->addSerializer(fn (PropertyReflector $property) => $property->getIterableType() !== null, ArrayOfObjectsSerializer::class);
    }
}
