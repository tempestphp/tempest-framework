<?php

namespace Tests\Tempest\Integration\Database\Builder;

use Tempest\Database\MigratesUp;
use Tempest\Database\Migrations\CreateMigrationsTable;
use Tempest\Database\PrimaryKey;
use Tempest\Database\QueryStatement;
use Tempest\Database\QueryStatements\CreateTableStatement;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

use function Tempest\Database\query;

final class CustomPrimaryKeyTest extends FrameworkIntegrationTestCase
{
    public function test_model_with_custom_primary_key_name(): void
    {
        $this->database->migrate(CreateMigrationsTable::class, CreateCustomPrimaryKeyUserModelTable::class);

        $frieren = query(CustomPrimaryKeyUserModel::class)->create(name: 'Frieren', magic: 'Time Magic');

        $this->assertInstanceOf(CustomPrimaryKeyUserModel::class, $frieren);
        $this->assertInstanceOf(PrimaryKey::class, $frieren->uuid);
        $this->assertSame('Frieren', $frieren->name);
        $this->assertSame('Time Magic', $frieren->magic);

        $retrieved = query(CustomPrimaryKeyUserModel::class)->get($frieren->uuid);
        $this->assertNotNull($retrieved);
        $this->assertSame('Frieren', $retrieved->name);
        $this->assertTrue($frieren->uuid->equals($retrieved->uuid));
    }

    public function test_update_or_create_with_custom_primary_key(): void
    {
        $this->database->migrate(CreateMigrationsTable::class, CreateCustomPrimaryKeyUserModelTable::class);

        $frieren = query(CustomPrimaryKeyUserModel::class)->create(name: 'Frieren', magic: 'Time Magic');

        $updated = query(CustomPrimaryKeyUserModel::class)->updateOrCreate(
            find: ['name' => 'Frieren'],
            update: ['magic' => 'Advanced Time Magic'],
        );

        $this->assertTrue($frieren->uuid->equals($updated->uuid));
        $this->assertSame('Advanced Time Magic', $updated->magic);
    }

    public function test_model_without_id_property_still_works(): void
    {
        $this->database->migrate(CreateMigrationsTable::class, CreateModelWithoutIdMigration::class);

        $model = query(ModelWithoutId::class)->new(name: 'Test');
        $this->assertInstanceOf(ModelWithoutId::class, $model);
        $this->assertSame('Test', $model->name);
    }
}

final class CustomPrimaryKeyUserModel
{
    public ?PrimaryKey $uuid = null;

    public function __construct(
        public string $name,
        public string $magic,
    ) {}
}

final class ModelWithMultipleIds
{
    public ?PrimaryKey $uuid = null;

    public ?PrimaryKey $external_id = null;

    public function __construct(
        public string $name = 'test',
    ) {}
}

final class ModelWithoutId
{
    public function __construct(
        public string $name,
    ) {}
}

final class CreateCustomPrimaryKeyUserModelTable implements MigratesUp
{
    public string $name = '001_create_user_model';

    public function up(): QueryStatement
    {
        return CreateTableStatement::forModel(CustomPrimaryKeyUserModel::class)
            ->primary(name: 'uuid')
            ->text('name')
            ->text('magic');
    }
}

final class CreateModelWithoutIdMigration implements MigratesUp
{
    public string $name = '002_create_model_without_id';

    public function up(): QueryStatement
    {
        return CreateTableStatement::forModel(ModelWithoutId::class)
            ->text('name');
    }
}
