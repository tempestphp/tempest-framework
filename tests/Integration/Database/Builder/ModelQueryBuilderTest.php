<?php

namespace Tests\Tempest\Integration\Database\Builder;

use Tempest\Database\Builder\QueryBuilders\CountQueryBuilder;
use Tempest\Database\Builder\QueryBuilders\DeleteQueryBuilder;
use Tempest\Database\Builder\QueryBuilders\InsertQueryBuilder;
use Tempest\Database\Builder\QueryBuilders\SelectQueryBuilder;
use Tempest\Database\Builder\QueryBuilders\UpdateQueryBuilder;
use Tempest\Database\Exceptions\ModelDidNotHavePrimaryColumn;
use Tempest\Database\Migrations\CreateMigrationsTable;
use Tempest\Database\PrimaryKey;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

use function Tempest\Database\model;

final class ModelQueryBuilderTest extends FrameworkIntegrationTestCase
{
    public function test_select(): void
    {
        $this->migrate(CreateMigrationsTable::class, TestModelWrapperMigration::class, TestModelWithoutIdMigration::class);

        model(TestUserModel::class)->create(name: 'Frieren');
        model(TestUserModel::class)->create(name: 'Fern');
        model(TestUserModelWithoutId::class)->create(name: 'Stark');

        $builderWithId = model(TestUserModel::class)->select();
        $builderWithoutId = model(TestUserModelWithoutId::class)->select();

        $this->assertInstanceOf(SelectQueryBuilder::class, $builderWithId);
        $this->assertInstanceOf(SelectQueryBuilder::class, $builderWithoutId);

        $resultsWithId = $builderWithId->all();
        $resultsWithoutId = $builderWithoutId->all();

        $this->assertCount(2, $resultsWithId);
        $this->assertInstanceOf(TestUserModel::class, $resultsWithId[0]);
        $this->assertInstanceOf(TestUserModel::class, $resultsWithId[1]);

        $this->assertCount(1, $resultsWithoutId);
        $this->assertInstanceOf(TestUserModelWithoutId::class, $resultsWithoutId[0]);
        $this->assertSame('Stark', $resultsWithoutId[0]->name);

        $builderWithSpecificColumns = model(TestUserModel::class)->select('name');
        $this->assertInstanceOf(SelectQueryBuilder::class, $builderWithSpecificColumns);

        $resultsWithSpecificColumns = $builderWithSpecificColumns->all();
        $this->assertCount(2, $resultsWithSpecificColumns);
        $this->assertInstanceOf(TestUserModel::class, $resultsWithSpecificColumns[0]);
        $this->assertNull($resultsWithSpecificColumns[0]->id);
        $this->assertSame('Frieren', $resultsWithSpecificColumns[0]->name);
    }

    public function test_insert(): void
    {
        $this->migrate(CreateMigrationsTable::class, TestModelWrapperMigration::class, TestModelWithoutIdMigration::class);

        $builderWithId = model(TestUserModel::class)->insert(name: 'Frieren');
        $builderWithoutId = model(TestUserModelWithoutId::class)->insert(name: 'Stark');

        $this->assertInstanceOf(InsertQueryBuilder::class, $builderWithId);
        $this->assertInstanceOf(InsertQueryBuilder::class, $builderWithoutId);

        $insertedId = $builderWithId->execute();
        $this->assertInstanceOf(PrimaryKey::class, $insertedId);

        $this->assertNull($builderWithoutId->execute());

        $retrieved = model(TestUserModel::class)->get($insertedId);
        $this->assertNotNull($retrieved);
        $this->assertSame('Frieren', $retrieved->name);

        $starkRecords = model(TestUserModelWithoutId::class)->select()->where('name', 'Stark')->all();
        $this->assertCount(1, $starkRecords);
        $this->assertSame('Stark', $starkRecords[0]->name);
    }

    public function test_update(): void
    {
        $this->migrate(CreateMigrationsTable::class, TestModelWrapperMigration::class, TestModelWithoutIdMigration::class);

        $createdWithId = model(TestUserModel::class)->create(name: 'Frieren');
        model(TestUserModelWithoutId::class)->create(name: 'Stark');

        $builderWithId = model(TestUserModel::class)->update(name: 'Eisen');
        $builderWithoutId = model(TestUserModelWithoutId::class)->update(name: 'Fern');

        $this->assertInstanceOf(UpdateQueryBuilder::class, $builderWithId);
        $this->assertInstanceOf(UpdateQueryBuilder::class, $builderWithoutId);

        $builderWithId->where('id', $createdWithId->id)->execute();
        $builderWithoutId->where('name', 'Stark')->execute();

        $retrieved = model(TestUserModel::class)->get($createdWithId->id);
        $this->assertNotNull($retrieved);
        $this->assertSame('Eisen', $retrieved->name);

        $starkRecords = model(TestUserModelWithoutId::class)->select()->where('name', 'Fern')->all();
        $this->assertCount(1, $starkRecords);
        $this->assertSame('Fern', $starkRecords[0]->name);
    }

    public function test_delete(): void
    {
        $this->migrate(CreateMigrationsTable::class, TestModelWrapperMigration::class, TestModelWithoutIdMigration::class);

        $createdWithId = model(TestUserModel::class)->create(name: 'Frieren');
        model(TestUserModel::class)->create(name: 'Fern');
        model(TestUserModelWithoutId::class)->create(name: 'Stark');
        model(TestUserModelWithoutId::class)->create(name: 'Eisen');

        $builderWithId = model(TestUserModel::class)->delete();
        $builderWithoutId = model(TestUserModelWithoutId::class)->delete();

        $this->assertInstanceOf(DeleteQueryBuilder::class, $builderWithId);
        $this->assertInstanceOf(DeleteQueryBuilder::class, $builderWithoutId);

        $builderWithId->where('id', $createdWithId->id)->execute();
        $builderWithoutId->where('name', 'Stark')->execute();

        $remainingWithId = model(TestUserModel::class)->select()->all();
        $this->assertCount(1, $remainingWithId);
        $this->assertSame('Fern', $remainingWithId[0]->name);

        $remainingWithoutId = model(TestUserModelWithoutId::class)->select()->all();
        $this->assertCount(1, $remainingWithoutId);
        $this->assertSame('Eisen', $remainingWithoutId[0]->name);
    }

    public function test_count(): void
    {
        $this->migrate(CreateMigrationsTable::class, TestModelWrapperMigration::class, TestModelWithoutIdMigration::class);

        model(TestUserModel::class)->create(name: 'Frieren');
        model(TestUserModel::class)->create(name: 'Fern');
        model(TestUserModel::class)->create(name: 'Stark');
        model(TestUserModelWithoutId::class)->create(name: 'Eisen');
        model(TestUserModelWithoutId::class)->create(name: 'Heiter');

        $builderWithId = model(TestUserModel::class)->count();
        $builderWithoutId = model(TestUserModelWithoutId::class)->count();

        $this->assertInstanceOf(CountQueryBuilder::class, $builderWithId);
        $this->assertInstanceOf(CountQueryBuilder::class, $builderWithoutId);

        $countWithId = $builderWithId->execute();
        $countWithoutId = $builderWithoutId->execute();

        $this->assertSame(3, $countWithId);
        $this->assertSame(2, $countWithoutId);

        $countFilteredWithId = model(TestUserModel::class)->count()->where('name', 'Frieren')->execute();
        $countFilteredWithoutId = model(TestUserModelWithoutId::class)->count()->where('name', 'Eisen')->execute();

        $this->assertSame(1, $countFilteredWithId);
        $this->assertSame(1, $countFilteredWithoutId);
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
        $this->assertInstanceOf(PrimaryKey::class, $createdWithId->id);
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
        $this->assertInstanceOf(PrimaryKey::class, $resultWithId->id);
        $this->assertSame('Aura', $resultWithId->name);
    }

    public function test_get_with_string_id(): void
    {
        $this->migrate(CreateMigrationsTable::class, TestModelWrapperMigration::class);

        $created = model(TestUserModel::class)->create(name: 'Heiter');
        $retrieved = model(TestUserModel::class)->get((string) $created->id->value);

        $this->assertNotNull($retrieved);
        $this->assertSame('Heiter', $retrieved->name);
        $this->assertTrue($created->id->equals($retrieved->id));
    }

    public function test_get_with_int_id(): void
    {
        $this->migrate(CreateMigrationsTable::class, TestModelWrapperMigration::class);

        $created = model(TestUserModel::class)->create(name: 'Eisen');
        $retrieved = model(TestUserModel::class)->get($created->id->value);

        $this->assertNotNull($retrieved);
        $this->assertSame('Eisen', $retrieved->name);
        $this->assertTrue($created->id->equals($retrieved->id));
    }

    public function test_get_returns_null_for_non_existent_id(): void
    {
        $this->migrate(CreateMigrationsTable::class, TestModelWrapperMigration::class);

        $result = model(TestUserModel::class)->get(new PrimaryKey(999));

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

    public function test_custom_primary_key_name(): void
    {
        $this->migrate(CreateMigrationsTable::class, TestModelWithCustomPrimaryKeyMigration::class);

        $created = model(TestUserModelWithCustomPrimaryKey::class)->create(name: 'Fern');

        $this->assertInstanceOf(TestUserModelWithCustomPrimaryKey::class, $created);
        $this->assertInstanceOf(PrimaryKey::class, $created->uuid);
        $this->assertSame('Fern', $created->name);

        $retrieved = model(TestUserModelWithCustomPrimaryKey::class)->get($created->uuid);
        $this->assertNotNull($retrieved);
        $this->assertSame('Fern', $retrieved->name);
        $this->assertTrue($created->uuid->equals($retrieved->uuid));
    }

    public function test_custom_primary_key_update_or_create(): void
    {
        $this->migrate(CreateMigrationsTable::class, TestModelWithCustomPrimaryKeyMigration::class);

        $original = model(TestUserModelWithCustomPrimaryKey::class)->create(name: 'Stark');

        $updated = model(TestUserModelWithCustomPrimaryKey::class)->updateOrCreate(
            find: ['name' => 'Stark'],
            update: ['name' => 'Stark the Strong'],
        );

        $this->assertTrue($original->uuid->equals($updated->uuid));
        $this->assertSame('Stark the Strong', $updated->name);
    }
}

final class TestUserModel
{
    public ?PrimaryKey $id = null;

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

final class TestUserModelWithCustomPrimaryKey
{
    public ?PrimaryKey $uuid = null;

    public function __construct(
        public string $name,
    ) {}
}

final class TestModelWithCustomPrimaryKeyMigration implements DatabaseMigration
{
    public string $name = '002_test_model_with_custom_primary_key';

    public function up(): QueryStatement
    {
        return CreateTableStatement::forModel(TestUserModelWithCustomPrimaryKey::class)
            ->primary(name: 'uuid')
            ->text('name');
    }

    public function down(): ?QueryStatement
    {
        return null;
    }
}
