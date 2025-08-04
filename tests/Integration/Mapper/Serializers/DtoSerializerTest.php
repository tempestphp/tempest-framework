<?php

namespace Tests\Tempest\Integration\Mapper\Serializers;

use Tempest\Mapper\Exceptions\ValueCouldNotBeSerialized;
use Tempest\Mapper\MapperConfig;
use Tempest\Mapper\Serializers\DtoSerializer;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;
use Tests\Tempest\Integration\Mapper\Fixtures\MyObject;

final class DtoSerializerTest extends FrameworkIntegrationTestCase
{
    public function test_serialize(): void
    {
        $this->assertSame(
            json_encode(['type' => MyObject::class, 'data' => ['name' => 'test']]),
            new DtoSerializer(new MapperConfig())->serialize(new MyObject(name: 'test')),
        );
    }

    public function test_serialize_with_map(): void
    {
        $config = new MapperConfig()->serializeAs(MyObject::class, 'my-object');

        $this->assertSame(
            json_encode(['type' => 'my-object', 'data' => ['name' => 'test']]),
            new DtoSerializer($config)->serialize(new MyObject(name: 'test')),
        );
    }

    public function test_can_serialize_empty_array(): void
    {
        $result = new DtoSerializer(new MapperConfig())->serialize([]);

        $this->assertSame('[]', $result);
    }

    public function test_cannot_serialize_non_object_non_array(): void
    {
        $this->expectException(ValueCouldNotBeSerialized::class);

        new DtoSerializer(new MapperConfig())->serialize('string');
    }
}
