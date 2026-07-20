<?php

namespace Tests\Tempest\Integration\Mapper\Serializers;

use PHPUnit\Framework\Attributes\Test;
use Tempest\Mapper\Exceptions\ValueCouldNotBeSerialized;
use Tempest\Mapper\Serializers\ArrayOfObjectsSerializer;
use Tempest\Mapper\Serializers\EnumSerializer;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;
use Tests\Tempest\Integration\Mapper\Fixtures\ObjectWithSerializerProperties;

final class ArrayOfObjectsSerializerTest extends FrameworkIntegrationTestCase
{
    #[Test]
    public function serialize(): void
    {
        $this->assertSame(
            [
                [
                    'stringProp' => 'a',
                    'stringableProp' => 'a',
                    'intProp' => '1',
                    'nullableIntProp' => null,
                    'floatProp' => '0.1',
                    'nullableFloatProp' => null,
                    'boolProp' => 'true',
                    'nullableBoolProp' => null,
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
                    'unitEnum' => 'BAR',
                    'backedEnum' => 'foo',
                ],
            ],
            new ArrayOfObjectsSerializer()->serialize([new ObjectWithSerializerProperties()]),
        );
    }

    #[Test]
    public function only_arrays_allowed(): void
    {
        $this->expectException(ValueCouldNotBeSerialized::class);

        new EnumSerializer()->serialize('foo');
    }
}
