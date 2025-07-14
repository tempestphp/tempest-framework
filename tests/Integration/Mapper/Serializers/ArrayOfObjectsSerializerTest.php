<?php

namespace Tests\Tempest\Integration\Mapper\Serializers;

use PHPUnit\Framework\TestCase;
use Tempest\Mapper\Exceptions\ValueCouldNotBeSerialized;
use Tempest\Mapper\Serializers\ArrayOfObjectsSerializer;
use Tempest\Mapper\Serializers\EnumSerializer;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;
use Tests\Tempest\Integration\Mapper\Fixtures\ObjectWithSerializerProperties;

final class ArrayOfObjectsSerializerTest extends FrameworkIntegrationTestCase
{
    public function test_serialize(): void
    {
        $this->assertSame(
            [
                [
                    'stringProp' => 'a',
                    'stringableProp' => 'a',
                    'intProp' => '1',
                    'floatProp' => '0.1',
                    'boolProp' => 'true',
                    'arrayProp' => '["a"]',
                    'serializeWithProp' => 'aa',
                    'doubleStringProp' => 'aa',
                    'jsonSerializableObject' => [
                        0 => 'a',
                    ],
                    'serializableObject' => "O:60:\"Tests\Tempest\Integration\Mapper\Fixtures\SerializableObject\":1:{i:0;s:1:\"a\";}",
                    'nativeDateTimeImmutableProp' => '2025-01-01 00:00:00',
                    'nativeDateTimeProp' => '2025-01-01 00:00:00',
                    'nativeDateTimeInterfaceProp' => '2025-01-01 00:00:00',
                    'dateTimeProp' => '2025-01-01 00:00:00',
                ],
            ],
            new ArrayOfObjectsSerializer()->serialize([new ObjectWithSerializerProperties()]),
        );
    }

    public function test_only_arrays_allowed(): void
    {
        $this->expectException(ValueCouldNotBeSerialized::class);

        new EnumSerializer()->serialize('foo');
    }
}
