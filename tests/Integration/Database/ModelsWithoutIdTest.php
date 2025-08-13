<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Database;

use Tempest\Database\BelongsTo;
use Tempest\Database\DatabaseMigration;
use Tempest\Database\Exceptions\ModelDidNotHavePrimaryColumn;
use Tempest\Database\HasOne;
use Tempest\Database\IsDatabaseModel;
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
    public function test_save_creates_new_record_for_model_without_id(): void
    {
        $this->migrate(CreateMigrationsTable::class, CreateLogEntryMigration::class);

        $log = new LogEntry(level: 'INFO', message: 'Frieren discovered ancient magic', context: 'exploration');
        $savedLog = $log->save();

        $this->assertSame($log, $savedLog);
        $this->assertSame('INFO', $savedLog->level);
        $this->assertSame('Frieren discovered ancient magic', $savedLog->message);

        $allLogs = query(LogEntry::class)->all();
        $this->assertCount(1, $allLogs);
        $this->assertSame('INFO', $allLogs[0]->level);
    }

    public function test_save_always_inserts_for_models_without_id(): void
    {
        $this->migrate(CreateMigrationsTable::class, CreateLogEntryMigration::class);

        $log = new LogEntry(level: 'INFO', message: 'Original message', context: 'test');
        $log->save();

        // Models without primary keys always insert when save() is called
        $log->message = 'Modified message';
        $log->save();

        $allLogs = query(LogEntry::class)->all();
        $this->assertCount(2, $allLogs);
        $this->assertSame('Original message', $allLogs[0]->message);
        $this->assertSame('Modified message', $allLogs[1]->message);
    }

    public function test_update_model_without_id_with_specific_conditions(): void
    {
        $this->migrate(CreateMigrationsTable::class, CreateLogEntryMigration::class);

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
        $this->migrate(CreateMigrationsTable::class, CreateLogEntryMigration::class);

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
        $this->migrate(CreateMigrationsTable::class, CreateCacheEntryMigration::class);

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
        $this->migrate(CreateMigrationsTable::class, CreateLogEntryMigration::class);

        $this->expectException(ModelDidNotHavePrimaryColumn::class);
        $this->expectExceptionMessage('does not have a primary column defined, which is required for the `findById` method');

        query(LogEntry::class)->findById(id: 1);
    }

    public function test_get_method_throws_for_models_without_id(): void
    {
        $this->migrate(CreateMigrationsTable::class, CreateLogEntryMigration::class);

        $this->expectException(ModelDidNotHavePrimaryColumn::class);
        $this->expectExceptionMessage('does not have a primary column defined, which is required for the `get` method');

        query(LogEntry::class)->get(id: 1);
    }

    public function test_update_or_create_throws_for_models_without_id(): void
    {
        $this->migrate(CreateMigrationsTable::class, CreateLogEntryMigration::class);

        $this->expectException(ModelDidNotHavePrimaryColumn::class);
        $this->expectExceptionMessage('does not have a primary column defined, which is required for the `updateOrCreate` method');

        query(LogEntry::class)->updateOrCreate(
            find: ['level' => 'INFO'],
            update: ['message' => 'test'],
        );
    }

    public function test_model_with_mixed_id_and_non_id_properties(): void
    {
        $this->migrate(CreateMigrationsTable::class, CreateMixedModelMigration::class);

        $mixed = new MixedModel(
            regular_field: 'test',
            another_field: 'data',
        );

        $mixed->save();

        $this->assertInstanceOf(PrimaryKey::class, $mixed->id);
        $this->assertSame('test', $mixed->regular_field);

        $all = query(MixedModel::class)->all();
        $this->assertCount(1, $all);
        $this->assertInstanceOf(PrimaryKey::class, $all[0]->id);
        $this->assertSame('test', $all[0]->regular_field);
    }

    public function test_refresh_throws_for_models_without_id(): void
    {
        $this->migrate(CreateMigrationsTable::class, CreateLogEntryMigration::class);

        $log = new LogEntry(
            level: 'INFO',
            message: 'Frieren studies magic',
            context: 'training',
        );

        $this->expectException(ModelDidNotHavePrimaryColumn::class);
        $this->expectExceptionMessage('does not have a primary column defined, which is required for the `refresh` method');

        $log->refresh();
    }

    public function test_load_throws_for_models_without_id(): void
    {
        $this->migrate(CreateMigrationsTable::class, CreateLogEntryMigration::class);

        $log = new LogEntry(
            level: 'INFO',
            message: 'Frieren explores ruins',
            context: 'adventure',
        );

        $this->expectException(ModelDidNotHavePrimaryColumn::class);
        $this->expectExceptionMessage('does not have a primary column defined, which is required for the `load` method');

        $log->load('someRelation');
    }

    public function test_refresh_works_for_models_with_id(): void
    {
        $this->migrate(CreateMigrationsTable::class, CreateMixedModelMigration::class);

        $mixed = query(MixedModel::class)->create(
            regular_field: 'original',
            another_field: 'data',
        );

        query(MixedModel::class)
            ->update(regular_field: 'updated')
            ->where('id', $mixed->id->value)
            ->execute();

        $mixed->refresh();

        $this->assertSame('updated', $mixed->regular_field);
        $this->assertSame('data', $mixed->another_field);
    }

    public function test_refresh_works_for_models_with_unloaded_relation(): void
    {
        $this->migrate(
            CreateMigrationsTable::class,
            CreateTestUserMigration::class,
            CreateTestProfileMigration::class,
        );

        $user = query(TestUser::class)->create(
            name: 'Frieren',
            email: 'frieren@magic.elf',
        );

        query(TestProfile::class)->create(
            user: $user,
            bio: 'Ancient elf mage',
            age: 1000,
        );

        // Get user without loading the profile relation
        $userWithoutProfile = query(TestUser::class)->findById($user->id);

        $this->assertNull($userWithoutProfile->profile);

        // Update the user's name in the database
        query(TestUser::class)
            ->update(name: 'Frieren the Mage')
            ->where('id', $user->id->value)
            ->execute();

        // Refresh should work even with unloaded relations
        $userWithoutProfile->refresh();

        $this->assertSame('Frieren the Mage', $userWithoutProfile->name);
        $this->assertSame('frieren@magic.elf', $userWithoutProfile->email);
        $this->assertNull($userWithoutProfile->profile); // Relation should still be unloaded

        // Load the relation
        $userWithoutProfile->load('profile');

        $this->assertInstanceOf(TestProfile::class, $userWithoutProfile->profile);
        $this->assertSame('Ancient elf mage', $userWithoutProfile->profile->bio);
        $this->assertSame(1000, $userWithoutProfile->profile->age);

        $userWithoutProfile->refresh();

        $this->assertInstanceOf(TestProfile::class, $userWithoutProfile->profile);
    }

    public function test_load_works_for_models_with_id(): void
    {
        $this->migrate(CreateMigrationsTable::class, CreateMixedModelMigration::class);

        $mixed = query(MixedModel::class)->create(regular_field: 'test', another_field: 'data');
        $result = $mixed->load();

        $this->assertSame($mixed, $result);
        $this->assertSame('test', $mixed->regular_field);
    }

    public function test_load_with_relation_works_for_models_with_id(): void
    {
        $this->migrate(
            CreateMigrationsTable::class,
            CreateTestUserMigration::class,
            CreateTestProfileMigration::class,
        );

        $user = query(TestUser::class)->create(
            name: 'Frieren',
            email: 'frieren@magic.elf',
        );

        query(TestProfile::class)->create(
            user: $user,
            bio: 'Ancient elf mage who loves magic and collecting spells',
            age: 1000,
        );

        $userWithProfile = $user->load('profile');

        $this->assertSame($user, $userWithProfile);
        $this->assertSame('Frieren', $user->name);
        $this->assertInstanceOf(TestProfile::class, $user->profile);
        $this->assertSame('Ancient elf mage who loves magic and collecting spells', $user->profile->bio);
        $this->assertSame(1000, $user->profile->age);
    }

    // this may be a bug, but I'm adding a test just to be sure we don't break the behavior by mistake.
    // I believe ->load should just load the specified relations, but it also reloads all properties
    public function test_load_method_refreshes_all_properties_not_just_relations(): void
    {
        $this->migrate(
            CreateMigrationsTable::class,
            CreateTestUserMigration::class,
            CreateTestProfileMigration::class,
        );

        $user = query(TestUser::class)->create(
            name: 'Frieren',
            email: 'frieren@magic.elf',
        );

        query(TestProfile::class)->create(
            user: $user,
            bio: 'Ancient elf mage',
            age: 1000,
        );

        $userInstance = query(TestUser::class)->findById($user->id);
        $userInstance->name = 'Fern';

        query(TestUser::class)
            ->update(email: 'updated@magic.elf')
            ->where('id', $user->id->value)
            ->execute();

        $userInstance->load('profile');

        $this->assertSame('Frieren', $userInstance->name); // "Fern" was discarded here
        $this->assertSame('updated@magic.elf', $userInstance->email);
        $this->assertInstanceOf(TestProfile::class, $userInstance->profile);
        $this->assertNotNull($userInstance->profile->bio);
    }
}

final class LogEntry
{
    use IsDatabaseModel;

    public function __construct(
        public string $level,
        public string $message,
        public string $context,
    ) {}
}

#[Table('cache_entries')]
final class CacheEntry
{
    use IsDatabaseModel;

    public function __construct(
        public string $cache_key,
        public string $cache_value,
        public int $ttl,
    ) {}
}

final class MixedModel
{
    use IsDatabaseModel;

    public ?PrimaryKey $id = null;

    public function __construct(
        public string $regular_field,
        public string $another_field,
    ) {}
}

final class TestUser
{
    use IsDatabaseModel;

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
    use IsDatabaseModel;

    public ?PrimaryKey $id = null;

    #[BelongsTo(ownerJoin: 'user_id')]
    public ?TestUser $user;

    public function __construct(
        public string $bio,
        public int $age,
    ) {}
}

final class CreateLogEntryMigration implements DatabaseMigration
{
    public string $name = '001_create_log_entries';

    public function up(): QueryStatement
    {
        return CreateTableStatement::forModel(LogEntry::class)
            ->text('level')
            ->text('message')
            ->text('context');
    }

    public function down(): ?QueryStatement
    {
        return null;
    }
}

final class CreateCacheEntryMigration implements DatabaseMigration
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

    public function down(): ?QueryStatement
    {
        return null;
    }
}

final class CreateMixedModelMigration implements DatabaseMigration
{
    public string $name = '005_create_mixed_models';

    public function up(): QueryStatement
    {
        return CreateTableStatement::forModel(MixedModel::class)
            ->primary()
            ->text('regular_field')
            ->text('another_field');
    }

    public function down(): ?QueryStatement
    {
        return null;
    }
}

final class CreateTestUserMigration implements DatabaseMigration
{
    public string $name = '007_create_test_users';

    public function up(): QueryStatement
    {
        return CreateTableStatement::forModel(TestUser::class)
            ->primary()
            ->text('name')
            ->text('email');
    }

    public function down(): ?QueryStatement
    {
        return null;
    }
}

final class CreateTestProfileMigration implements DatabaseMigration
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

    public function down(): ?QueryStatement
    {
        return null;
    }
}
