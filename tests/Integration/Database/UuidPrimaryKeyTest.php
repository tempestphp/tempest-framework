<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Database;

use PHPUnit\Framework\Attributes\Test;
use Tempest\Database\IsDatabaseModel;
use Tempest\Database\MigratesUp;
use Tempest\Database\Migrations\CreateMigrationsTable;
use Tempest\Database\PrimaryKey;
use Tempest\Database\QueryStatement;
use Tempest\Database\QueryStatements\CreateTableStatement;
use Tempest\Database\Table;
use Tempest\Database\Uuid;
use Tempest\Support\Random;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

use function Tempest\Database\query;

/**
 * @internal
 */
final class UuidPrimaryKeyTest extends FrameworkIntegrationTestCase
{
    #[Test]
    public function uuid_primary_key_auto_generation(): void
    {
        $this->database->migrate(CreateMigrationsTable::class, CreateModelWithUuidTableMigration::class);

        $mage = query(DatabaseModelWithUuid::class)->create(
            name: 'Frieren',
            race: 'Human',
        );

        $this->assertInstanceOf(DatabaseModelWithUuid::class, $mage);
        $this->assertInstanceOf(PrimaryKey::class, $mage->uuid);
        $this->assertTrue(Random\is_uuid($mage->uuid->value));
        $this->assertSame('Frieren', $mage->name);
        $this->assertSame('Human', $mage->race);
    }

    #[Test]
    public function uuid_primary_key_save_method(): void
    {
        $this->database->migrate(CreateMigrationsTable::class, CreateModelWithUuidTableMigration::class);

        $mage = new DatabaseModelWithUuid(name: 'Fern', race: 'Human');
        $savedMage = $mage->save();

        $this->assertSame($mage, $savedMage);
        $this->assertInstanceOf(PrimaryKey::class, $mage->uuid);
        $this->assertTrue(Random\is_uuid($mage->uuid->value));
        $this->assertSame('Fern', $mage->name);
    }

    #[Test]
    public function uuid_primary_key_retrieval(): void
    {
        $this->database->migrate(CreateMigrationsTable::class, CreateModelWithUuidTableMigration::class);

        $mage = query(DatabaseModelWithUuid::class)->create(
            name: 'Frieren',
            race: 'Elf',
        );

        $retrieved = query(DatabaseModelWithUuid::class)->get($mage->uuid);
        $this->assertNotNull($retrieved);
        $this->assertSame('Frieren', $retrieved->name);
        $this->assertTrue($mage->uuid->equals($retrieved->uuid));
    }

    #[Test]
    public function uuid_primary_key_update_or_create(): void
    {
        $this->database->migrate(CreateMigrationsTable::class, CreateModelWithUuidTableMigration::class);

        $original = query(DatabaseModelWithUuid::class)->create(
            name: 'Himmel',
            race: 'Elf',
        );

        $updated = query(DatabaseModelWithUuid::class)->updateOrCreate(
            find: ['name' => 'Himmel'],
            update: ['race' => 'Human'],
        );

        $this->assertTrue($original->uuid->equals($updated->uuid));
        $this->assertSame('Human', $updated->race);
    }

    #[Test]
    public function uuid_primary_key_manual_assignment(): void
    {
        $this->database->migrate(CreateMigrationsTable::class, CreateModelWithUuidTableMigration::class);

        $uuid = Random\uuid();

        $mage = new DatabaseModelWithUuid(name: 'Stark', race: 'Human');
        $mage->uuid = new PrimaryKey($uuid);
        $mage->save();

        $this->assertSame($uuid, $mage->uuid->value);
    }

    #[Test]
    public function uuid_primary_key_without_is_database_model_trait(): void
    {
        $this->database->migrate(CreateMigrationsTable::class, CreateModelWithUuidTableMigration::class);

        $mage = query(ModelWithUuid::class)->create(
            name: 'Frieren',
            race: 'Elf',
        );

        $this->assertInstanceOf(ModelWithUuid::class, $mage);
        $this->assertInstanceOf(PrimaryKey::class, $mage->uuid);
        $this->assertTrue(Random\is_uuid($mage->uuid->value));
        $this->assertSame('Frieren', $mage->name);
        $this->assertSame('Elf', $mage->race);

        $retrieved = query(ModelWithUuid::class)->get($mage->uuid);

        $this->assertNotNull($retrieved);
        $this->assertTrue($mage->uuid->equals($retrieved->uuid));
    }
}

#[Table('model')]
final class DatabaseModelWithUuid
{
    use IsDatabaseModel;

    #[Uuid]
    public PrimaryKey $uuid;

    public function __construct(
        public string $name,
        public string $race,
    ) {}
}

#[Table('model')]
final class ModelWithUuid
{
    #[Uuid]
    public PrimaryKey $uuid;

    public function __construct(
        public string $name,
        public string $race,
    ) {}
}

final class CreateModelWithUuidTableMigration implements MigratesUp
{
    public string $name = '001_create_model_with_uuid';

    public function up(): QueryStatement
    {
        return new CreateTableStatement('model')
            ->uuid(name: 'uuid')
            ->text('name')
            ->text('race');
    }
}
