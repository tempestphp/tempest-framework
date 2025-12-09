<?php

namespace Tests\Tempest\Integration\Database\ModelInspector;

use Tempest\Database\Casters\DataTransferObjectCaster;
use Tempest\Database\IsDatabaseModel;
use Tempest\Database\Serializers\DataTransferObjectSerializer;
use Tempest\Database\Virtual;
use Tempest\Mapper\CastWith;
use Tempest\Mapper\SerializeWith;
use Tests\Tempest\Integration\IntegrationTestCase;

use function Tempest\Database\inspect;

final class ModelInspectorTest extends IntegrationTestCase
{
    public function test_virtual_array_is_never_a_relation(): void
    {
        $this->assertFalse(inspect(ModelInspectorTestModelWithVirtualHasMany::class)->isRelation('dtos'));
    }

    public function test_virtual_property_is_never_a_relation(): void
    {
        $this->assertFalse(inspect(ModelInspectorTestModelWithVirtualDto::class)->isRelation('dto'));
    }

    public function test_serialized_property_type_is_never_a_relation(): void
    {
        $this->assertFalse(inspect(ModelInspectorTestModelWithSerializedDto::class)->isRelation('dto'));
    }

    public function test_serialized_property_is_never_a_relation(): void
    {
        $this->assertFalse(inspect(ModelInspectorTestModelWithSerializedDtoProperty::class)->isRelation('dto'));
    }
}

final class ModelInspectorTestDtoForModelWithVirtual
{
    public function __construct(
        public string $data,
    ) {}
}

final class ModelInspectorTestModelWithVirtualHasMany
{
    use IsDatabaseModel;

    #[Virtual]
    /** @var \Tests\Tempest\Integration\Database\ModelInspector\ModelInspectorTestDtoForModelWithVirtual[] $dto */
    public array $dtos;
}

final class ModelInspectorTestModelWithVirtualDto
{
    use IsDatabaseModel;

    #[Virtual]
    public ModelInspectorTestDtoForModelWithVirtual $dto;
}

#[CastWith(DataTransferObjectCaster::class)]
#[SerializeWith(DataTransferObjectSerializer::class)]
final class ModelInspectorTestDtoForModelWithSerializer
{
    public function __construct(
        public string $data,
    ) {}
}

final class ModelInspectorTestModelWithSerializedDto
{
    use IsDatabaseModel;

    public ModelInspectorTestDtoForModelWithSerializer $dto;
}

final class ModelInspectorTestDtoForModelWithSerializerOnProperty
{
    public function __construct(
        public string $data,
    ) {}
}

final class ModelInspectorTestModelWithSerializedDtoProperty
{
    use IsDatabaseModel;

    #[SerializeWith(DataTransferObjectSerializer::class)]
    public ModelInspectorTestDtoForModelWithSerializerOnProperty $dto;
}
