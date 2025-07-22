<?php

namespace Tests\Tempest\Integration\Mapper;

use BackedEnum;
use Tempest\Mapper\CasterFactory;
use Tempest\Mapper\Casters\BooleanCaster;
use Tempest\Mapper\Casters\DateTimeCaster;
use Tempest\Mapper\Casters\EnumCaster;
use Tempest\Mapper\Casters\FloatCaster;
use Tempest\Mapper\Casters\IntegerCaster;
use Tempest\Mapper\Casters\NativeDateTimeCaster;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;
use Tests\Tempest\Integration\Mapper\Fixtures\ObjectWithSerializerProperties;
use UnitEnum;

use function Tempest\reflect;

final class CasterFactoryTest extends FrameworkIntegrationTestCase
{
    public function test_for_property(): void
    {
        $factory = $this->container->get(CasterFactory::class);
        $class = reflect(ObjectWithSerializerProperties::class);

        $this->assertInstanceOf(IntegerCaster::class, $factory->forProperty($class->getProperty('intProp')));
        $this->assertInstanceOf(FloatCaster::class, $factory->forProperty($class->getProperty('floatProp')));
        $this->assertInstanceOf(BooleanCaster::class, $factory->forProperty($class->getProperty('boolProp')));
        $this->assertInstanceOf(NativeDateTimeCaster::class, $factory->forProperty($class->getProperty('nativeDateTimeImmutableProp')));
        $this->assertInstanceOf(NativeDateTimeCaster::class, $factory->forProperty($class->getProperty('nativeDateTimeProp')));
        $this->assertInstanceOf(NativeDateTimeCaster::class, $factory->forProperty($class->getProperty('nativeDateTimeInterfaceProp')));
        $this->assertInstanceOf(DateTimeCaster::class, $factory->forProperty($class->getProperty('dateTimeProp')));
        $this->assertInstanceOf(EnumCaster::class, $factory->forProperty($class->getProperty('unitEnum')));
        $this->assertInstanceOf(EnumCaster::class, $factory->forProperty($class->getProperty('backedEnum')));
    }
}
