<?php

namespace Tests\Tempest\Integration\Mapper;

use DateTime as NativeDateTime;
use DateTimeImmutable as NativeDateTimeImmutable;
use Tempest\DateTime\DateTime;
use Tempest\Mapper\SerializerFactory;
use Tempest\Mapper\Serializers\ArrayToJsonSerializer;
use Tempest\Mapper\Serializers\BooleanSerializer;
use Tempest\Mapper\Serializers\DateTimeSerializer;
use Tempest\Mapper\Serializers\FloatSerializer;
use Tempest\Mapper\Serializers\IntegerSerializer;
use Tempest\Mapper\Serializers\NativeDateTimeSerializer;
use Tempest\Mapper\Serializers\SerializableSerializer;
use Tempest\Mapper\Serializers\StringSerializer;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;
use Tests\Tempest\Integration\Mapper\Fixtures\DoubleStringSerializer;
use Tests\Tempest\Integration\Mapper\Fixtures\JsonSerializableObject;
use Tests\Tempest\Integration\Mapper\Fixtures\NestedObjectB;
use Tests\Tempest\Integration\Mapper\Fixtures\ObjectWithSerializerProperties;
use Tests\Tempest\Integration\Mapper\Fixtures\SerializableObject;

use function Tempest\Reflection\reflect;

final class SerializerFactoryTest extends FrameworkIntegrationTestCase
{
    public function test_for_value(): void
    {
        /** @var SerializerFactory $factory */
        $factory = $this->container->get(SerializerFactory::class);

        $this->assertInstanceOf(StringSerializer::class, $factory->forValue('string'));
        $this->assertInstanceOf(StringSerializer::class, $factory->forValue(\Tempest\Support\str('string')));
        $this->assertInstanceOf(IntegerSerializer::class, $factory->forValue(1));
        $this->assertInstanceOf(FloatSerializer::class, $factory->forValue(1.1));
        $this->assertInstanceOf(BooleanSerializer::class, $factory->forValue(true));
        $this->assertInstanceOf(ArrayToJsonSerializer::class, $factory->forValue([]));
        $this->assertInstanceOf(SerializableSerializer::class, $factory->forValue(new JsonSerializableObject()));
        $this->assertInstanceOf(SerializableSerializer::class, $factory->forValue(new SerializableObject()));
        $this->assertInstanceOf(NativeDateTimeSerializer::class, $factory->forValue(new NativeDateTime()));
        $this->assertInstanceOf(NativeDateTimeSerializer::class, $factory->forValue(new NativeDateTimeImmutable()));
        $this->assertInstanceOf(DateTimeSerializer::class, $factory->forValue(DateTime::now()));
        $this->assertNull($factory->forValue(null));
        $this->assertNull($factory->forValue(new NestedObjectB('name')));
    }

    public function test_for_property(): void
    {
        $factory = $this->container->get(SerializerFactory::class);

        $class = reflect(ObjectWithSerializerProperties::class);

        $this->assertInstanceOf(StringSerializer::class, $factory->forProperty($class->getProperty('stringProp')));
        $this->assertInstanceOf(StringSerializer::class, $factory->forProperty($class->getProperty('stringableProp')));
        $this->assertInstanceOf(IntegerSerializer::class, $factory->forProperty($class->getProperty('intProp')));
        $this->assertInstanceOf(FloatSerializer::class, $factory->forProperty($class->getProperty('floatProp')));
        $this->assertInstanceOf(BooleanSerializer::class, $factory->forProperty($class->getProperty('boolProp')));
        $this->assertInstanceOf(ArrayToJsonSerializer::class, $factory->forProperty($class->getProperty('arrayProp')));
        $this->assertInstanceOf(DoubleStringSerializer::class, $factory->forProperty($class->getProperty('serializeWithProp')));
        $this->assertInstanceOf(DoubleStringSerializer::class, $factory->forProperty($class->getProperty('doubleStringProp')));
        $this->assertInstanceOf(SerializableSerializer::class, $factory->forProperty($class->getProperty('jsonSerializableObject')));
        $this->assertInstanceOf(SerializableSerializer::class, $factory->forProperty($class->getProperty('serializableObject')));
        $this->assertInstanceOf(NativeDateTimeSerializer::class, $factory->forProperty($class->getProperty('nativeDateTimeImmutableProp')));
        $this->assertInstanceOf(NativeDateTimeSerializer::class, $factory->forProperty($class->getProperty('nativeDateTimeProp')));
        $this->assertInstanceOf(NativeDateTimeSerializer::class, $factory->forProperty($class->getProperty('nativeDateTimeInterfaceProp')));
        $this->assertInstanceOf(DateTimeSerializer::class, $factory->forProperty($class->getProperty('dateTimeProp')));
    }
}
