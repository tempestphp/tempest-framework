<?php

namespace Tests\Tempest\Integration\Database\ModelInspector;

use PHPUnit\Framework\Attributes\Test;
use Tempest\Database\Casters\DataTransferObjectCaster;
use Tempest\Database\IsDatabaseModel;
use Tempest\Database\PrimaryKey;
use Tempest\Database\Serializers\DataTransferObjectSerializer;
use Tempest\Database\Virtual;
use Tempest\Mapper\CastWith;
use Tempest\Mapper\SerializeWith;
use Tests\Tempest\Integration\IntegrationTestCase;

use function Tempest\Database\inspect;

final class ModelInspectorTest extends IntegrationTestCase
{
    #[Test]
    public function virtual_array_is_never_a_relation(): void
    {
        $this->assertFalse(inspect(ModelInspectorTestModelWithVirtualHasMany::class)->isRelation('dtos'));
    }

    #[Test]
    public function virtual_property_is_never_a_relation(): void
    {
        $this->assertFalse(inspect(ModelInspectorTestModelWithVirtualDto::class)->isRelation('dto'));
    }

    #[Test]
    public function serialized_property_type_is_never_a_relation(): void
    {
        $this->assertFalse(inspect(ModelInspectorTestModelWithSerializedDto::class)->isRelation('dto'));
    }

    #[Test]
    public function serialized_property_is_never_a_relation(): void
    {
        $this->assertFalse(inspect(ModelInspectorTestModelWithSerializedDtoProperty::class)->isRelation('dto'));
    }

    #[Test]
    public function select_doesnt_contain_duplicate_fields(): void
    {
        $model = inspect(HasRelatedModelWithId::class);

        $selectedFields = $model->getSelectFields();

        $this->assertEqualsCanonicalizing(
            expected: ['name', 'relation_id'],
            actual: $selectedFields->toArray(),
        );
    }
}

final class RelationWithId
{
    public PrimaryKey $id;

    public string $name;
}

final class HasRelatedModelWithId
{
    public RelationWithId $relation;

    public function __construct(
        public string $name,
        public int $relation_id,
    ) {}
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
