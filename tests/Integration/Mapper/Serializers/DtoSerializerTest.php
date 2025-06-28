<?php

namespace Tests\Tempest\Integration\Mapper\Serializers;

use Tempest\Mapper\Exceptions\ValueCouldNotBeSerialized;
use Tempest\Mapper\Serializers\DtoSerializer;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;
use Tests\Tempest\Integration\Mapper\Fixtures\MyObject;

final class DtoSerializerTest extends FrameworkIntegrationTestCase
{
    public function test_serialize(): void
    {
        $this->assertSame(
            json_encode(['type' => MyObject::class, 'data' => ['name' => 'test']]),
            new DtoSerializer()->serialize(new MyObject(name: 'test')),
        );
    }

    public function test_cannot_serialize_non_object(): void
    {
        $this->expectException(ValueCouldNotBeSerialized::class);

        new DtoSerializer()->serialize([]);
    }
}
