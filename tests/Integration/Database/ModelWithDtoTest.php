<?php

namespace Integration\Database;

use Tempest\Database\DatabaseMigration;
use Tempest\Database\Migrations\CreateMigrationsTable;
use Tempest\Database\QueryStatement;
use Tempest\Database\QueryStatements\CreateTableStatement;
use Tests\Tempest\Integration\Database\Fixtures\DtoForModelWithSerializer;
use Tests\Tempest\Integration\Database\Fixtures\ModelWithSerializedDto;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

use function Tempest\Database\model;

final class ModelWithDtoTest extends FrameworkIntegrationTestCase
{
    public function test_model_inspector_is_relation_with_dto(): void
    {
        $definition = model(ModelWithSerializedDto::class);
        $this->assertFalse($definition->isRelation('dto'));
    }

    public function test_dto_is_skipped_as_relation(): void
    {
        $migration = new class implements DatabaseMigration {
            public string $name = '000_model_with_dto';

            public function up(): QueryStatement
            {
                return CreateTableStatement::forModel(ModelWithSerializedDto::class)
                    ->primary()
                    ->dto('dto');
            }

            public function down(): null
            {
                return null;
            }
        };

        $this->migrate(CreateMigrationsTable::class, $migration);

        ModelWithSerializedDto::new(dto: new DtoForModelWithSerializer('test'))->save();

        $model = ModelWithSerializedDto::get(1);

        $this->assertSame('test', $model->dto->data);
    }
}
