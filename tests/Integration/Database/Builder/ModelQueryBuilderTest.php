<?php

namespace Tests\Tempest\Integration\Database\Builder;

use Tempest\Database\Builder\QueryBuilders\CountQueryBuilder;
use Tempest\Database\Builder\QueryBuilders\DeleteQueryBuilder;
use Tempest\Database\Builder\QueryBuilders\InsertQueryBuilder;
use Tempest\Database\Builder\QueryBuilders\SelectQueryBuilder;
use Tempest\Database\Builder\QueryBuilders\UpdateQueryBuilder;
use Tempest\Database\Exceptions\ModelDidNotHavePrimaryColumn;
use Tempest\Database\Id;
use Tempest\Database\Migrations\CreateMigrationsTable;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

use function Tempest\Database\model;

final class ModelQueryBuilderTest extends FrameworkIntegrationTestCase
{
    public function test_select(): void
    {
        $this->migrate(CreateMigrationsTable::class, TestModelWrapperMigration::class, TestModelWithoutIdMigration::class);

        $builderWithId = model(TestUserModel::class)->select();
        $builderWithoutId = model(TestUserModelWithoutId::class)->select();

        $this->assertInstanceOf(SelectQueryBuilder::class, $builderWithId);
        $this->assertInstanceOf(SelectQueryBuilder::class, $builderWithoutId);
    }

    public function test_insert(): void
    {
        $this->migrate(CreateMigrationsTable::class, TestModelWrapperMigration::class, TestModelWithoutIdMigration::class);

        $builderWithId = model(TestUserModel::class)->insert();
        $builderWithoutId = model(TestUserModelWithoutId::class)->insert();

        $this->assertInstanceOf(InsertQueryBuilder::class, $builderWithId);
        $this->assertInstanceOf(InsertQueryBuilder::class, $builderWithoutId);
    }

    public function test_update(): void
    {
        $this->migrate(CreateMigrationsTable::class, TestModelWrapperMigration::class, TestModelWithoutIdMigration::class);

        $builderWithId = model(TestUserModel::class)->update();
        $builderWithoutId = model(TestUserModelWithoutId::class)->update();

        $this->assertInstanceOf(UpdateQueryBuilder::class, $builderWithId);
        $this->assertInstanceOf(UpdateQueryBuilder::class, $builderWithoutId);
    }

    public function test_delete(): void
    {
        $this->migrate(CreateMigrationsTable::class, TestModelWrapperMigration::class, TestModelWithoutIdMigration::class);

        $builderWithId = model(TestUserModel::class)->delete();
        $builderWithoutId = model(TestUserModelWithoutId::class)->delete();

        $this->assertInstanceOf(DeleteQueryBuilder::class, $builderWithId);
        $this->assertInstanceOf(DeleteQueryBuilder::class, $builderWithoutId);
    }

    public function test_count(): void
    {
        $this->migrate(CreateMigrationsTable::class, TestModelWrapperMigration::class, TestModelWithoutIdMigration::class);

        $builderWithId = model(TestUserModel::class)->count();
        $builderWithoutId = model(TestUserModelWithoutId::class)->count();

        $this->assertInstanceOf(CountQueryBuilder::class, $builderWithId);
        $this->assertInstanceOf(CountQueryBuilder::class, $builderWithoutId);
    }

    public function test_new(): void
    {
        $modelWithId = model(TestUserModel::class)->new(name: 'Frieren');
        $modelWithoutId = model(TestUserModelWithoutId::class)->new(name: 'Fern');

        $this->assertInstanceOf(TestUserModel::class, $modelWithId);
        $this->assertSame('Frieren', $modelWithId->name);

        $this->assertInstanceOf(TestUserModelWithoutId::class, $modelWithoutId);
        $this->assertSame('Fern', $modelWithoutId->name);
    }

    public function test_resolve_with_id_model(): void
    {
        $this->migrate(CreateMigrationsTable::class, TestModelWrapperMigration::class);

        $created = model(TestUserModel::class)->create(name: 'Stark');
        $resolved = model(TestUserModel::class)->resolve($created->id);

        $this->assertInstanceOf(TestUserModel::class, $resolved);
        $this->assertSame('Stark', $resolved->name);
        $this->assertTrue($created->id->equals($resolved->id));
    }

    public function test_resolve_throws_for_model_without_id(): void
    {
        $this->migrate(CreateMigrationsTable::class, TestModelWithoutIdMigration::class);

        $this->expectException(ModelDidNotHavePrimaryColumn::class);
        $this->expectExceptionMessage(
            "`Tests\Tempest\Integration\Database\Builder\TestUserModelWithoutId` does not have a primary column defined, which is required for the `resolve` method.",
        );

        model(TestUserModelWithoutId::class)->resolve(1);
    }

    public function test_get_with_id_model(): void
    {
        $this->migrate(CreateMigrationsTable::class, TestModelWrapperMigration::class);

        $created = model(TestUserModel::class)->create(name: 'Himmel');
        $retrieved = model(TestUserModel::class)->get($created->id);

        $this->assertNotNull($retrieved);
        $this->assertSame('Himmel', $retrieved->name);
        $this->assertTrue($created->id->equals($retrieved->id));
    }

    public function test_get_throws_for_model_without_id(): void
    {
        $this->migrate(CreateMigrationsTable::class, TestModelWithoutIdMigration::class);

        $this->expectException(ModelDidNotHavePrimaryColumn::class);
        $this->expectExceptionMessage(
            "`Tests\Tempest\Integration\Database\Builder\TestUserModelWithoutId` does not have a primary column defined, which is required for the `get` method.",
        );

        model(TestUserModelWithoutId::class)->get(1);
    }

    public function test_all(): void
    {
        $this->migrate(CreateMigrationsTable::class, TestModelWrapperMigration::class, TestModelWithoutIdMigration::class);

        model(TestUserModel::class)->create(name: 'Fern');
        model(TestUserModel::class)->create(name: 'Stark');

        model(TestUserModelWithoutId::class)->create(name: 'Eisen');
        model(TestUserModelWithoutId::class)->create(name: 'Heiter');

        $allWithId = model(TestUserModel::class)->all();
        $allWithoutId = model(TestUserModelWithoutId::class)->all();

        $this->assertCount(2, $allWithId);
        $this->assertInstanceOf(TestUserModel::class, $allWithId[0]);
        $this->assertInstanceOf(TestUserModel::class, $allWithId[1]);

        $this->assertCount(2, $allWithoutId);
        $this->assertInstanceOf(TestUserModelWithoutId::class, $allWithoutId[0]);
        $this->assertInstanceOf(TestUserModelWithoutId::class, $allWithoutId[1]);
    }

    public function test_find(): void
    {
        $this->migrate(CreateMigrationsTable::class, TestModelWrapperMigration::class, TestModelWithoutIdMigration::class);

        model(TestUserModel::class)->create(name: 'Frieren');
        model(TestUserModel::class)->create(name: 'Fern');

        model(TestUserModelWithoutId::class)->create(name: 'Ubel');
        model(TestUserModelWithoutId::class)->create(name: 'Land');

        $builderWithId = model(TestUserModel::class)->find(name: 'Frieren');
        $builderWithoutId = model(TestUserModelWithoutId::class)->find(name: 'Ubel');

        $this->assertInstanceOf(SelectQueryBuilder::class, $builderWithId);
        $this->assertInstanceOf(SelectQueryBuilder::class, $builderWithoutId);

        $resultWithId = $builderWithId->first();
        $resultWithoutId = $builderWithoutId->first();

        $this->assertNotNull($resultWithId);
        $this->assertSame('Frieren', $resultWithId->name);

        $this->assertNotNull($resultWithoutId);
        $this->assertSame('Ubel', $resultWithoutId->name);
    }

    public function test_create(): void
    {
        $this->migrate(CreateMigrationsTable::class, TestModelWrapperMigration::class, TestModelWithoutIdMigration::class);

        $createdWithId = model(TestUserModel::class)->create(name: 'Ubel');
        $createdWithoutId = model(TestUserModelWithoutId::class)->create(name: 'Serie');

        $this->assertInstanceOf(TestUserModel::class, $createdWithId);
        $this->assertInstanceOf(Id::class, $createdWithId->id);
        $this->assertSame('Ubel', $createdWithId->name);

        $this->assertInstanceOf(TestUserModelWithoutId::class, $createdWithoutId);
        $this->assertSame('Serie', $createdWithoutId->name);
    }

    public function test_find_or_new_finds_existing(): void
    {
        $this->migrate(CreateMigrationsTable::class, TestModelWrapperMigration::class, TestModelWithoutIdMigration::class);

        $existingWithId = model(TestUserModel::class)->create(name: 'Serie');
        $existingWithoutId = model(TestUserModelWithoutId::class)->create(name: 'Macht');

        $resultWithId = model(TestUserModel::class)->findOrNew(
            find: ['name' => 'Serie'],
            update: ['name' => 'Updated Serie'],
        );

        $resultWithoutId = model(TestUserModelWithoutId::class)->findOrNew(
            find: ['name' => 'Macht'],
            update: ['name' => 'Updated Macht'],
        );

        $this->assertInstanceOf(TestUserModel::class, $resultWithId);
        $this->assertTrue($existingWithId->id->equals($resultWithId->id));
        $this->assertSame('Updated Serie', $resultWithId->name);

        $this->assertInstanceOf(TestUserModelWithoutId::class, $resultWithoutId);
        $this->assertSame('Updated Macht', $resultWithoutId->name);
    }

    public function test_find_or_new_creates_new(): void
    {
        $this->migrate(CreateMigrationsTable::class, TestModelWrapperMigration::class, TestModelWithoutIdMigration::class);

        $resultWithId = model(TestUserModel::class)->findOrNew(
            find: ['name' => 'NonExistent'],
            update: ['name' => 'Updated Name'],
        );

        $resultWithoutId = model(TestUserModelWithoutId::class)->findOrNew(
            find: ['name' => 'NonExistent'],
            update: ['name' => 'Updated Name'],
        );

        $this->assertInstanceOf(TestUserModel::class, $resultWithId);
        $this->assertFalse(isset($resultWithId->id));
        $this->assertSame('Updated Name', $resultWithId->name);

        $this->assertInstanceOf(TestUserModelWithoutId::class, $resultWithoutId);
        $this->assertSame('Updated Name', $resultWithoutId->name);
    }

    public function test_update_or_create_updates_existing(): void
    {
        $this->migrate(CreateMigrationsTable::class, TestModelWrapperMigration::class);

        $existingWithId = model(TestUserModel::class)->create(name: 'Qual');

        $resultWithId = model(TestUserModel::class)->updateOrCreate(
            find: ['name' => 'Qual'],
            update: ['name' => 'Updated Qual'],
        );

        $this->assertInstanceOf(TestUserModel::class, $resultWithId);
        $this->assertTrue($existingWithId->id->equals($resultWithId->id));
        $this->assertSame('Updated Qual', $resultWithId->name);
    }

    public function test_update_or_create_creates_new(): void
    {
        $this->migrate(CreateMigrationsTable::class, TestModelWrapperMigration::class);

        $resultWithId = model(TestUserModel::class)->updateOrCreate(
            find: ['name' => 'NonExistent'],
            update: ['name' => 'Aura'],
        );

        $this->assertInstanceOf(TestUserModel::class, $resultWithId);
        $this->assertInstanceOf(Id::class, $resultWithId->id);
        $this->assertSame('Aura', $resultWithId->name);
    }

    public function test_get_with_string_id(): void
    {
        $this->migrate(CreateMigrationsTable::class, TestModelWrapperMigration::class);

        $created = model(TestUserModel::class)->create(name: 'Heiter');
        $retrieved = model(TestUserModel::class)->get((string) $created->id->id);

        $this->assertNotNull($retrieved);
        $this->assertSame('Heiter', $retrieved->name);
        $this->assertTrue($created->id->equals($retrieved->id));
    }

    public function test_get_with_int_id(): void
    {
        $this->migrate(CreateMigrationsTable::class, TestModelWrapperMigration::class);

        $created = model(TestUserModel::class)->create(name: 'Eisen');
        $retrieved = model(TestUserModel::class)->get($created->id->id);

        $this->assertNotNull($retrieved);
        $this->assertSame('Eisen', $retrieved->name);
        $this->assertTrue($created->id->equals($retrieved->id));
    }

    public function test_get_returns_null_for_non_existent_id(): void
    {
        $this->migrate(CreateMigrationsTable::class, TestModelWrapperMigration::class);

        $result = model(TestUserModel::class)->get(new Id(999));

        $this->assertNull($result);
    }

    public function test_find_by_id_throws_for_model_without_id(): void
    {
        $this->migrate(CreateMigrationsTable::class, TestModelWithoutIdMigration::class);

        $this->expectException(ModelDidNotHavePrimaryColumn::class);
        $this->expectExceptionMessage(
            "`Tests\Tempest\Integration\Database\Builder\TestUserModelWithoutId` does not have a primary column defined, which is required for the `findById` method.",
        );

        model(TestUserModelWithoutId::class)->findById(1);
    }

    public function test_update_or_create_throws_for_model_without_id(): void
    {
        $this->migrate(CreateMigrationsTable::class, TestModelWithoutIdMigration::class);

        $this->expectException(ModelDidNotHavePrimaryColumn::class);
        $this->expectExceptionMessage(
            "`Tests\Tempest\Integration\Database\Builder\TestUserModelWithoutId` does not have a primary column defined, which is required for the `updateOrCreate` method.",
        );

        model(TestUserModelWithoutId::class)->updateOrCreate(
            find: ['name' => 'Denken'],
            update: ['name' => 'Updated Denken'],
        );
    }
}

final class TestUserModel
{
    public ?Id $id = null;

    public function __construct(
        public string $name,
    ) {}
}

final class TestUserModelWithoutId
{
    public function __construct(
        public string $name,
    ) {}
}

use Tempest\Database\DatabaseMigration;
use Tempest\Database\QueryStatement;
use Tempest\Database\QueryStatements\CreateTableStatement;

final class TestModelWrapperMigration implements DatabaseMigration
{
    public string $name = '000_test_model_wrapper';

    public function up(): QueryStatement
    {
        return CreateTableStatement::forModel(TestUserModel::class)
            ->primary()
            ->text('name');
    }

    public function down(): ?QueryStatement
    {
        return null;
    }
}

final class TestModelWithoutIdMigration implements DatabaseMigration
{
    public string $name = '001_test_model_without_id';

    public function up(): QueryStatement
    {
        return CreateTableStatement::forModel(TestUserModelWithoutId::class)
            ->text('name');
    }

    public function down(): ?QueryStatement
    {
        return null;
    }
}
