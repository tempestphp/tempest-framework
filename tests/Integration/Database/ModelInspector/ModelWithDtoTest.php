<?php

namespace Tests\Tempest\Integration\Database\ModelInspector;

use Tempest\Database\Casters\DataTransferObjectCaster;
use Tempest\Database\IsDatabaseModel;
use Tempest\Database\MigratesUp;
use Tempest\Database\Migrations\CreateMigrationsTable;
use Tempest\Database\QueryStatement;
use Tempest\Database\QueryStatements\CreateTableStatement;
use Tempest\Database\Serializers\DataTransferObjectSerializer;
use Tempest\Mapper\CastWith;
use Tempest\Mapper\SerializeWith;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

use function Tempest\Database\inspect;

final class ModelWithDtoTest extends FrameworkIntegrationTestCase
{
    public function test_model_inspector_is_relation_with_dto(): void
    {
        $definition = inspect(ModelWithDtoTestModelWithSerializedDto::class);
        $this->assertFalse($definition->isRelation('dto'));
    }

    public function test_dto_is_skipped_as_relation(): void
    {
        $migration = new class implements MigratesUp {
            public string $name = '000_model_with_dto';

            public function up(): QueryStatement
            {
                return CreateTableStatement::forModel(ModelWithDtoTestModelWithSerializedDto::class)
                    ->primary()
                    ->dto('dto');
            }
        };

        $this->database->migrate(CreateMigrationsTable::class, $migration);

        ModelWithDtoTestModelWithSerializedDto::new(dto: new ModelWithDtoTestDtoForModelWithSerializer('test'))->save();

        $model = ModelWithDtoTestModelWithSerializedDto::get(1);

        $this->assertSame('test', $model->dto->data);
    }
}

#[CastWith(DataTransferObjectCaster::class)]
#[SerializeWith(DataTransferObjectSerializer::class)]
final class ModelWithDtoTestDtoForModelWithSerializer
{
    public function __construct(
        public string $data,
    ) {}
}

final class ModelWithDtoTestModelWithSerializedDto
{
    use IsDatabaseModel;

    public ModelWithDtoTestDtoForModelWithSerializer $dto;
}
