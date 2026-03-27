<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Database;

use PHPUnit\Framework\Attributes\Test;
use Tempest\Database\BelongsTo;
use Tempest\Database\Eager;
use Tempest\Database\HasMany;
use Tempest\Database\IsDatabaseModel;
use Tempest\Database\MigratesUp;
use Tempest\Database\Migrations\CreateMigrationsTable;
use Tempest\Database\QueryStatement;
use Tempest\Database\QueryStatements\CreateTableStatement;
use Tempest\Database\Table;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

use function Tempest\Database\query;

/**
 * @internal
 */
final class MultipleSameTableBelongsToTest extends FrameworkIntegrationTestCase
{
    #[Test]
    public function two_belongs_to_same_table_with_explicit_with(): void
    {
        $this->database->migrate(
            CreateMigrationsTable::class,
            CreateSameTableTestUserMigration::class,
            CreateSameTableTestRoleMigration::class,
        );

        $alice = query(model: SameTableTestUser::class)->create(name: 'Alice');
        $bob = query(model: SameTableTestUser::class)->create(name: 'Bob');

        query(model: SameTableTestRole::class)
            ->create(
                code: 'admin',
                created_by: $alice->id->value,
                updated_by: $bob->id->value,
            );

        $role = query(model: SameTableTestRole::class)
            ->select()
            ->with('createdBy', 'updatedBy')
            ->first();

        $this->assertSame(expected: 'admin', actual: $role->code);
        $this->assertInstanceOf(expected: SameTableTestUser::class, actual: $role->createdBy);
        $this->assertInstanceOf(expected: SameTableTestUser::class, actual: $role->updatedBy);
        $this->assertSame(expected: 'Alice', actual: $role->createdBy->name);
        $this->assertSame(expected: 'Bob', actual: $role->updatedBy->name);
    }

    #[Test]
    public function two_eager_belongs_to_same_table(): void
    {
        $this->database->migrate(
            CreateMigrationsTable::class,
            CreateSameTableTestUserMigration::class,
            CreateSameTableTestEagerRoleMigration::class,
        );

        $alice = query(model: SameTableTestUser::class)->create(name: 'Alice');
        $bob = query(model: SameTableTestUser::class)->create(name: 'Bob');

        query(model: SameTableTestEagerRole::class)
            ->create(
                code: 'player',
                created_by: $alice->id->value,
                updated_by: $bob->id->value,
            );

        $role = query(model: SameTableTestEagerRole::class)
            ->select()
            ->first();

        $this->assertSame(expected: 'player', actual: $role->code);
        $this->assertInstanceOf(expected: SameTableTestUser::class, actual: $role->createdBy);
        $this->assertInstanceOf(expected: SameTableTestUser::class, actual: $role->updatedBy);
        $this->assertSame(expected: 'Alice', actual: $role->createdBy->name);
        $this->assertSame(expected: 'Bob', actual: $role->updatedBy->name);
    }

    #[Test]
    public function parent_to_child_with_two_eager_to_same_table_and_subchild(): void
    {
        $this->database->migrate(
            CreateMigrationsTable::class,
            CreateSameTableTestUserMigration::class,
            CreateSameTableTestEagerRoleMigration::class,
            CreateSameTableTestTaskMigration::class,
        );

        $alice = query(model: SameTableTestUser::class)->create(name: 'Alice');
        $bob = query(model: SameTableTestUser::class)->create(name: 'Bob');

        $role = query(model: SameTableTestEagerRole::class)
            ->create(
                code: 'admin',
                created_by: $alice->id->value,
                updated_by: $bob->id->value,
            );

        query(model: SameTableTestTask::class)
            ->create(
                title: 'Task 1',
                role_id: $role->id->value,
            );
        query(model: SameTableTestTask::class)
            ->create(
                title: 'Task 2',
                role_id: $role->id->value,
            );

        $task = query(model: SameTableTestTask::class)
            ->select()
            ->with('role', 'role.createdBy', 'role.updatedBy')
            ->first();

        $this->assertSame(expected: 'Task 1', actual: $task->title);
        $this->assertInstanceOf(expected: SameTableTestEagerRole::class, actual: $task->role);
        $this->assertSame(expected: 'admin', actual: $task->role->code);
        $this->assertInstanceOf(expected: SameTableTestUser::class, actual: $task->role->createdBy);
        $this->assertInstanceOf(expected: SameTableTestUser::class, actual: $task->role->updatedBy);
        $this->assertSame(expected: 'Alice', actual: $task->role->createdBy->name);
        $this->assertSame(expected: 'Bob', actual: $task->role->updatedBy->name);
    }
}

#[Table('same_table_test_users')]
final class SameTableTestUser
{
    use IsDatabaseModel;

    public function __construct(
        public string $name,
    ) {}
}

#[Table('same_table_test_roles')]
final class SameTableTestRole
{
    use IsDatabaseModel;

    #[BelongsTo(ownerJoin: 'created_by')]
    public ?SameTableTestUser $createdBy = null;

    #[BelongsTo(ownerJoin: 'updated_by')]
    public ?SameTableTestUser $updatedBy = null;

    public function __construct(
        public string $code,
        public ?int $created_by = null,
        public ?int $updated_by = null,
    ) {}
}

#[Table('same_table_test_eager_roles')]
final class SameTableTestEagerRole
{
    use IsDatabaseModel;

    #[Eager]
    #[BelongsTo(ownerJoin: 'created_by')]
    public ?SameTableTestUser $createdBy = null;

    #[Eager]
    #[BelongsTo(ownerJoin: 'updated_by')]
    public ?SameTableTestUser $updatedBy = null;

    /** @var \Tests\Tempest\Integration\Database\SameTableTestTask[] */
    #[HasMany(ownerJoin: 'role_id')]
    public array $tasks = [];

    public function __construct(
        public string $code,
        public ?int $created_by = null,
        public ?int $updated_by = null,
    ) {}
}

#[Table('same_table_test_tasks')]
final class SameTableTestTask
{
    use IsDatabaseModel;

    #[BelongsTo(ownerJoin: 'role_id')]
    public ?SameTableTestEagerRole $role = null;

    public function __construct(
        public string $title,
        public ?int $role_id = null,
    ) {}
}

final class CreateSameTableTestUserMigration implements MigratesUp
{
    public string $name = '001_create_same_table_test_users';

    public function up(): QueryStatement
    {
        return CreateTableStatement::forModel(modelClass: SameTableTestUser::class)
            ->primary()
            ->text(name: 'name');
    }
}

final class CreateSameTableTestRoleMigration implements MigratesUp
{
    public string $name = '002_create_same_table_test_roles';

    public function up(): QueryStatement
    {
        return CreateTableStatement::forModel(modelClass: SameTableTestRole::class)
            ->primary()
            ->text(name: 'code')
            ->belongsTo('same_table_test_roles.created_by', 'same_table_test_users.id')
            ->belongsTo('same_table_test_roles.updated_by', 'same_table_test_users.id');
    }
}

final class CreateSameTableTestEagerRoleMigration implements MigratesUp
{
    public string $name = '002_create_same_table_test_eager_roles';

    public function up(): QueryStatement
    {
        return CreateTableStatement::forModel(modelClass: SameTableTestEagerRole::class)
            ->primary()
            ->text(name: 'code')
            ->belongsTo('same_table_test_eager_roles.created_by', 'same_table_test_users.id')
            ->belongsTo('same_table_test_eager_roles.updated_by', 'same_table_test_users.id');
    }
}

final class CreateSameTableTestTaskMigration implements MigratesUp
{
    public string $name = '003_create_same_table_test_tasks';

    public function up(): QueryStatement
    {
        return CreateTableStatement::forModel(modelClass: SameTableTestTask::class)
            ->primary()
            ->text(name: 'title')
            ->belongsTo('same_table_test_tasks.role_id', 'same_table_test_eager_roles.id');
    }
}
