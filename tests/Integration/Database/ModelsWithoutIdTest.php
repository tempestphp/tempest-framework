<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Database;

use Tempest\Database\BelongsTo;
use Tempest\Database\Exceptions\ModelDidNotHavePrimaryColumn;
use Tempest\Database\HasOne;
use Tempest\Database\MigratesUp;
use Tempest\Database\Migrations\CreateMigrationsTable;
use Tempest\Database\PrimaryKey;
use Tempest\Database\QueryStatement;
use Tempest\Database\QueryStatements\CreateTableStatement;
use Tempest\Database\Table;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

use function Tempest\Database\query;

/**
 * @internal
 */
final class ModelsWithoutIdTest extends FrameworkIntegrationTestCase
{
    public function test_update_model_without_id_with_specific_conditions(): void
    {
        $this->database->migrate(CreateMigrationsTable::class, CreateLogEntryMigration::class);

        query(LogEntry::class)->create(
            level: 'INFO',
            message: 'Himmel was here',
            context: 'memory',
        );

        query(LogEntry::class)
            ->update(level: 'NOSTALGIC')
            ->where('context', 'memory')
            ->execute();

        $updatedLog = query(LogEntry::class)
            ->find(context: 'memory')
            ->first();

        $this->assertSame('NOSTALGIC', $updatedLog->level);
        $this->assertSame('Himmel was here', $updatedLog->message);
    }

    public function test_delete_operations_on_models_without_id(): void
    {
        $this->database->migrate(CreateMigrationsTable::class, CreateLogEntryMigration::class);

        query(LogEntry::class)->create(
            level: 'TEMP',
            message: 'Temporary debug info',
            context: 'debug',
        );

        query(LogEntry::class)->create(
            level: 'IMPORTANT',
            message: 'Frieren awakens',
            context: 'story',
        );

        $this->assertCount(2, query(LogEntry::class)->all());

        query(LogEntry::class)
            ->delete()
            ->where('level', 'TEMP')
            ->execute();

        $remaining = query(LogEntry::class)->all();
        $this->assertCount(1, $remaining);
        $this->assertSame('IMPORTANT', $remaining[0]->level);
    }

    public function test_model_without_id_with_unique_constraints(): void
    {
        $this->database->migrate(CreateMigrationsTable::class, CreateCacheEntryMigration::class);

        query(CacheEntry::class)->create(
            cache_key: 'spell_fire',
            cache_value: 'flame_magic_data',
            ttl: 3600,
        );

        query(CacheEntry::class)
            ->update(cache_value: 'updated_flame_data')
            ->where('cache_key', 'spell_fire')
            ->execute();

        $updatedData = query(CacheEntry::class)
            ->find(cache_key: 'spell_fire')
            ->first();

        $this->assertSame('updated_flame_data', $updatedData->cache_value);
    }

    public function test_relationship_methods_throw_for_models_without_id(): void
    {
        $this->database->migrate(CreateMigrationsTable::class, CreateLogEntryMigration::class);

        $this->expectException(ModelDidNotHavePrimaryColumn::class);
        $this->expectExceptionMessage('does not have a primary column defined, which is required for the `findById` method');

        query(LogEntry::class)->findById(id: 1);
    }

    public function test_get_method_throws_for_models_without_id(): void
    {
        $this->database->migrate(CreateMigrationsTable::class, CreateLogEntryMigration::class);

        $this->expectException(ModelDidNotHavePrimaryColumn::class);
        $this->expectExceptionMessage('does not have a primary column defined, which is required for the `get` method');

        query(LogEntry::class)->get(id: 1);
    }

    public function test_update_or_create_throws_for_models_without_id(): void
    {
        $this->database->migrate(CreateMigrationsTable::class, CreateLogEntryMigration::class);

        $this->expectException(ModelDidNotHavePrimaryColumn::class);
        $this->expectExceptionMessage('does not have a primary column defined, which is required for the `updateOrCreate` method');

        query(LogEntry::class)->updateOrCreate(
            find: ['level' => 'INFO'],
            update: ['message' => 'test'],
        );
    }

    public function test_model_with_mixed_id_and_non_id_properties(): void
    {
        $this->database->migrate(CreateMigrationsTable::class, CreateMixedModelMigration::class);

        $mixed = query(MixedModel::class)->create(
            regular_field: 'test',
            another_field: 'data',
        );

        $this->assertInstanceOf(PrimaryKey::class, $mixed->id);
        $this->assertSame('test', $mixed->regular_field);

        $all = query(MixedModel::class)->all();
        $this->assertCount(1, $all);
        $this->assertInstanceOf(PrimaryKey::class, $all[0]->id);
        $this->assertSame('test', $all[0]->regular_field);
    }
}

final class LogEntry
{
    public function __construct(
        public string $level,
        public string $message,
        public string $context,
    ) {}
}

#[Table('cache_entries')]
final class CacheEntry
{
    public function __construct(
        public string $cache_key,
        public string $cache_value,
        public int $ttl,
    ) {}
}

final class MixedModel
{
    public ?PrimaryKey $id = null;

    public function __construct(
        public string $regular_field,
        public string $another_field,
    ) {}
}

final class TestUser
{
    public ?PrimaryKey $id = null;

    #[HasOne(ownerJoin: 'user_id')]
    public ?TestProfile $profile = null;

    public function __construct(
        public string $name,
        public string $email,
    ) {}
}

final class TestProfile
{
    public ?PrimaryKey $id = null;

    #[BelongsTo(ownerJoin: 'user_id')]
    public ?TestUser $user;

    public function __construct(
        public string $bio,
        public int $age,
    ) {}
}

final class CreateLogEntryMigration implements MigratesUp
{
    public string $name = '001_create_log_entries';

    public function up(): QueryStatement
    {
        return CreateTableStatement::forModel(LogEntry::class)
            ->text('level')
            ->text('message')
            ->text('context');
    }
}

final class CreateCacheEntryMigration implements MigratesUp
{
    public string $name = '003_create_cache_entries';

    public function up(): QueryStatement
    {
        return CreateTableStatement::forModel(CacheEntry::class)
            ->string('cache_key')
            ->string('cache_value')
            ->integer('ttl')
            ->unique('cache_key');
    }
}

final class CreateMixedModelMigration implements MigratesUp
{
    public string $name = '005_create_mixed_models';

    public function up(): QueryStatement
    {
        return CreateTableStatement::forModel(MixedModel::class)
            ->primary()
            ->text('regular_field')
            ->text('another_field');
    }
}

final class CreateTestUserMigration implements MigratesUp
{
    public string $name = '007_create_test_users';

    public function up(): QueryStatement
    {
        return CreateTableStatement::forModel(TestUser::class)
            ->primary()
            ->text('name')
            ->text('email');
    }
}

final class CreateTestProfileMigration implements MigratesUp
{
    public string $name = '009_create_test_profiles';

    public function up(): QueryStatement
    {
        return CreateTableStatement::forModel(TestProfile::class)
            ->primary()
            ->belongsTo('test_profiles.user_id', 'test_users.id')
            ->text('bio')
            ->integer('age');
    }
}
