<?php

namespace Tests\Tempest\Integration\Mapper;

use Tempest\Mapper\SerializerFactory;
use Tempest\Mapper\Serializers\ArrayToJsonSerializer;
use Tempest\Mapper\Serializers\BooleanSerializer;
use Tempest\Mapper\Serializers\DateTimeSerializer;
use Tempest\Mapper\Serializers\FloatSerializer;
use Tempest\Mapper\Serializers\IntegerSerializer;
use Tempest\Mapper\Serializers\SerializableSerializer;
use Tempest\Mapper\Serializers\StringSerializer;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;
use Tests\Tempest\Integration\Mapper\Fixtures\DoubleStringSerializer;
use Tests\Tempest\Integration\Mapper\Fixtures\ObjectWithSerializerProperties;
use function Tempest\reflect;

final class SerializerFactoryTest extends FrameworkIntegrationTestCase
{
    public function test_serialize(): void
    {
        $factory = new SerializerFactory();

        $class = reflect(ObjectWithSerializerProperties::class);

        $this->assertInstanceOf(StringSerializer::class, $factory->forProperty($class->getProperty('stringableProp')));
        $this->assertInstanceOf(StringSerializer::class, $factory->forProperty($class->getProperty('stringProp')));
        $this->assertInstanceOf(IntegerSerializer::class, $factory->forProperty($class->getProperty('intProp')));
        $this->assertInstanceOf(FloatSerializer::class, $factory->forProperty($class->getProperty('floatProp')));
        $this->assertInstanceOf(BooleanSerializer::class, $factory->forProperty($class->getProperty('boolProp')));
        $this->assertInstanceOf(ArrayToJsonSerializer::class, $factory->forProperty($class->getProperty('arrayProp')));
        $this->assertInstanceOf(DoubleStringSerializer::class, $factory->forProperty($class->getProperty('serializeWithProp')));
        $this->assertInstanceOf(DoubleStringSerializer::class, $factory->forProperty($class->getProperty('doubleStringProp')));
        $this->assertInstanceOf(SerializableSerializer::class, $factory->forProperty($class->getProperty('jsonSerializableObject')));
        $this->assertInstanceOf(SerializableSerializer::class, $factory->forProperty($class->getProperty('serializableObject')));
        $this->assertInstanceOf(DateTimeSerializer::class, $factory->forProperty($class->getProperty('dateTimeImmutableProp')));
        $this->assertInstanceOf(DateTimeSerializer::class, $factory->forProperty($class->getProperty('dateTimeProp')));
        $this->assertInstanceOf(DateTimeSerializer::class, $factory->forProperty($class->getProperty('dateTimeInterfaceProp')));
    }
}