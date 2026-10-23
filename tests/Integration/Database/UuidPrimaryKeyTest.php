<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Database;

use PHPUnit\Framework\Attributes\Test;
use Tempest\Database\BelongsToMany;
use Tempest\Database\HasMany;
use Tempest\Database\IsDatabaseModel;
use Tempest\Database\MigratesUp;
use Tempest\Database\Migrations\CreateMigrationsTable;
use Tempest\Database\PrimaryKey;
use Tempest\Database\QueryStatement;
use Tempest\Database\QueryStatements\CreateTableStatement;
use Tempest\Database\QueryStatements\OnDelete;
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

    #[Test]
    public function uuid_primary_key_generated_for_iterable_insert(): void
    {
        $this->database->migrate(CreateMigrationsTable::class, CreateUuidRolesTableMigration::class);

        $id = query(UuidRole::class)->insert(name: 'admin')->execute();

        $this->assertInstanceOf(PrimaryKey::class, $id);
        $this->assertTrue(Random\is_uuid($id->value));

        $role = query(UuidRole::class)->get($id);

        $this->assertNotNull($role);
        $this->assertSame('admin', $role->name);
    }

    #[Test]
    public function batch_insert_attaches_relations_to_their_own_parent(): void
    {
        $this->database->migrate(
            CreateMigrationsTable::class,
            CreateUuidUsersTableMigration::class,
            CreateUuidRolesTableMigration::class,
            CreateUuidUserRoleTableMigration::class,
            CreateUuidPostsTableMigration::class,
        );

        $role = query(UuidRole::class)->create(name: 'admin');

        $frieren = UuidUser::new(name: 'Frieren');
        $frieren->posts = [new UuidPost(title: 'Post A')];
        $frieren->roles = [$role];

        $fern = UuidUser::new(name: 'Fern');
        $fern->posts = [new UuidPost(title: 'Post B')];
        $fern->roles = [$role];

        query(UuidUser::class)->insert($frieren, $fern)->execute();

        $userIds = [];

        foreach (query('uuid_users')->select()->all() as $user) {
            $userIds[$user['name']] = $user['id'];
        }

        $this->assertCount(2, $userIds);
        $this->assertNotSame($userIds['Frieren'], $userIds['Fern']);

        $posts = query('uuid_posts')->select()->all();

        $this->assertCount(2, $posts);

        foreach ($posts as $post) {
            $this->assertSame($userIds[$post['title'] === 'Post A' ? 'Frieren' : 'Fern'], $post['uuid_user_id']);
        }

        $pivotRows = query('uuid_user_role')->select()->all();
        $ownerIds = array_column($pivotRows, 'uuid_user_id');

        sort($ownerIds);

        $expectedOwnerIds = [$userIds['Frieren'], $userIds['Fern']];
        sort($expectedOwnerIds);

        $this->assertSame($expectedOwnerIds, $ownerIds);
    }

    #[Test]
    public function uuid_primary_key_belongs_to_many_pivot_uses_generated_uuid(): void
    {
        $this->database->migrate(
            CreateMigrationsTable::class,
            CreateUuidUsersTableMigration::class,
            CreateUuidRolesTableMigration::class,
            CreateUuidUserRoleTableMigration::class,
        );

        $role = query(UuidRole::class)->create(name: 'admin');

        $user = query(UuidUser::class)->create(
            name: 'Frieren',
            roles: [$role],
        );

        $this->assertTrue(Random\is_uuid($user->id->value));

        $pivotRows = query('uuid_user_role')->select()->all();

        $this->assertCount(1, $pivotRows);
        $this->assertSame($user->id->value, $pivotRows[0]['uuid_user_id']);
        $this->assertSame($role->id->value, $pivotRows[0]['uuid_role_id']);
    }

    #[Test]
    public function uuid_primary_key_has_many_uses_generated_uuid_as_foreign_key(): void
    {
        $this->database->migrate(
            CreateMigrationsTable::class,
            CreateUuidUsersTableMigration::class,
            CreateUuidPostsTableMigration::class,
        );

        $user = query(UuidUser::class)->create(
            name: 'Frieren',
            posts: [
                ['title' => 'Grimoire Notes'],
            ],
        );

        $this->assertTrue(Random\is_uuid($user->id->value));

        $posts = query('uuid_posts')->select()->all();

        $this->assertCount(1, $posts);
        $this->assertSame($user->id->value, $posts[0]['uuid_user_id']);
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

final class UuidUser
{
    use IsDatabaseModel;

    #[Uuid]
    public PrimaryKey $id;

    /** @var \Tests\Tempest\Integration\Database\UuidRole[] */
    #[BelongsToMany(pivot: 'uuid_user_role')]
    public array $roles = [];

    /** @var \Tests\Tempest\Integration\Database\UuidPost[] */
    #[HasMany]
    public array $posts = [];

    public function __construct(
        public string $name,
    ) {}
}

final class UuidRole
{
    use IsDatabaseModel;

    #[Uuid]
    public PrimaryKey $id;

    public function __construct(
        public string $name,
    ) {}
}

final class UuidPost
{
    use IsDatabaseModel;

    #[Uuid]
    public PrimaryKey $id;

    public ?PrimaryKey $uuid_user_id = null;

    public function __construct(
        public string $title,
    ) {}
}

final class CreateUuidUsersTableMigration implements MigratesUp
{
    public string $name = '100_create_uuid_users';

    public function up(): QueryStatement
    {
        return new CreateTableStatement('uuid_users')
            ->uuid()
            ->varchar('name');
    }
}

final class CreateUuidRolesTableMigration implements MigratesUp
{
    public string $name = '101_create_uuid_roles';

    public function up(): QueryStatement
    {
        return new CreateTableStatement('uuid_roles')
            ->uuid()
            ->varchar('name');
    }
}

final class CreateUuidUserRoleTableMigration implements MigratesUp
{
    public string $name = '102_create_uuid_user_role';

    public function up(): QueryStatement
    {
        return new CreateTableStatement('uuid_user_role')
            ->belongsToUuid('uuid_user_role.uuid_user_id', 'uuid_users.id', onDelete: OnDelete::CASCADE)
            ->belongsToUuid('uuid_user_role.uuid_role_id', 'uuid_roles.id', onDelete: OnDelete::CASCADE);
    }
}

final class CreateUuidPostsTableMigration implements MigratesUp
{
    public string $name = '103_create_uuid_posts';

    public function up(): QueryStatement
    {
        return new CreateTableStatement('uuid_posts')
            ->uuid()
            ->varchar('title')
            ->foreignUuid('uuid_user_id', constrainedOn: 'uuid_users', onDelete: OnDelete::CASCADE, nullable: true);
    }
}
