<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Database;

use PHPUnit\Framework\Attributes\Test;
use Tempest\Database\BelongsTo;
use Tempest\Database\Eager;
use Tempest\Database\HasMany;
use Tempest\Database\HasOne;
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
final class MultipleSameTableRelationsTest extends FrameworkIntegrationTestCase
{
    // BelongsTo

    #[Test]
    public function two_belongs_to_same_table_with_explicit_with(): void
    {
        $this->database->migrate(
            CreateMigrationsTable::class,
            CreateStUserMigration::class,
            CreateStRoleMigration::class,
        );

        $alice = query(model: StUser::class)->create(name: 'Alice');
        $bob = query(model: StUser::class)->create(name: 'Bob');
        query(model: StRole::class)->create(code: 'admin', createdBy: $alice, updatedBy: $bob);

        $role = query(model: StRole::class)
            ->select()
            ->with('createdBy', 'updatedBy')
            ->first();

        $this->assertSame('admin', $role->code);
        $this->assertInstanceOf(StUser::class, $role->createdBy);
        $this->assertInstanceOf(StUser::class, $role->updatedBy);
        $this->assertSame('Alice', $role->createdBy->name);
        $this->assertSame('Bob', $role->updatedBy->name);
    }

    #[Test]
    public function two_belongs_to_same_table_with_full_table_column_syntax(): void
    {
        $this->database->migrate(
            CreateMigrationsTable::class,
            CreateStUserMigration::class,
            CreateStFullSpecRoleMigration::class,
        );

        $alice = query(model: StUser::class)->create(name: 'Alice');
        $bob = query(model: StUser::class)->create(name: 'Bob');
        query(model: StFullSpecRole::class)->create(code: 'moderator', createdByUser: $alice, updatedByUser: $bob);

        $role = query(model: StFullSpecRole::class)
            ->select()
            ->with('createdByUser', 'updatedByUser')
            ->first();

        $this->assertSame('moderator', $role->code);
        $this->assertInstanceOf(StUser::class, $role->createdByUser);
        $this->assertInstanceOf(StUser::class, $role->updatedByUser);
        $this->assertSame('Alice', $role->createdByUser->name);
        $this->assertSame('Bob', $role->updatedByUser->name);
    }

    #[Test]
    public function two_eager_belongs_to_same_table(): void
    {
        $this->database->migrate(
            CreateMigrationsTable::class,
            CreateStUserMigration::class,
            CreateStEagerRoleMigration::class,
        );

        $alice = query(model: StUser::class)->create(name: 'Alice');
        $bob = query(model: StUser::class)->create(name: 'Bob');
        query(model: StEagerRole::class)->create(code: 'player', createdBy: $alice, updatedBy: $bob);

        $role = query(model: StEagerRole::class)
            ->select()
            ->first();

        $this->assertSame('player', $role->code);
        $this->assertInstanceOf(StUser::class, $role->createdBy);
        $this->assertInstanceOf(StUser::class, $role->updatedBy);
        $this->assertSame('Alice', $role->createdBy->name);
        $this->assertSame('Bob', $role->updatedBy->name);
    }

    #[Test]
    public function parent_to_child_with_two_eager_belongs_to_same_table(): void
    {
        $this->database->migrate(
            CreateMigrationsTable::class,
            CreateStUserMigration::class,
            CreateStEagerRoleMigration::class,
            CreateStTaskMigration::class,
        );

        $alice = query(model: StUser::class)->create(name: 'Alice');
        $bob = query(model: StUser::class)->create(name: 'Bob');
        $role = query(model: StEagerRole::class)->create(code: 'admin', createdBy: $alice, updatedBy: $bob);
        query(model: StTask::class)->create(title: 'Task 1', role: $role);

        $task = query(model: StTask::class)
            ->select()
            ->with('role', 'role.createdBy', 'role.updatedBy')
            ->first();

        $this->assertSame('Task 1', $task->title);
        $this->assertInstanceOf(StEagerRole::class, $task->role);
        $this->assertSame('admin', $task->role->code);
        $this->assertInstanceOf(StUser::class, $task->role->createdBy);
        $this->assertInstanceOf(StUser::class, $task->role->updatedBy);
        $this->assertSame('Alice', $task->role->createdBy->name);
        $this->assertSame('Bob', $task->role->updatedBy->name);
    }

    // HasMany

    #[Test]
    public function two_has_many_to_same_table(): void
    {
        $this->database->migrate(
            CreateMigrationsTable::class,
            CreateStUserMigration::class,
            CreateStMessageMigration::class,
        );

        $alice = query(model: StUser::class)->create(name: 'Alice');
        $bob = query(model: StUser::class)->create(name: 'Bob');
        query(model: StMessage::class)->create(body: 'Hello Bob', sender: $alice, receiver: $bob);
        query(model: StMessage::class)->create(body: 'Hi Alice', sender: $bob, receiver: $alice);

        $alice = query(model: StUser::class)
            ->select()
            ->with('sentMessages', 'receivedMessages')
            ->where('name', 'Alice')
            ->first();
        $this->assertCount(1, $alice->sentMessages);
        $this->assertCount(1, $alice->receivedMessages);
        $this->assertSame('Hello Bob', $alice->sentMessages[0]->body);
        $this->assertSame('Hi Alice', $alice->receivedMessages[0]->body);
    }

    // BelongsTo (HasOne-like pattern via BelongsTo on the owning side)

    #[Test]
    public function two_belongs_to_same_table_as_addresses(): void
    {
        $this->database->migrate(
            CreateMigrationsTable::class,
            CreateStAddressMigration::class,
            CreateStPersonMigration::class,
        );

        $home = query(model: StAddress::class)->create(street: '123 Home St');
        $work = query(model: StAddress::class)->create(street: '456 Work Ave');
        query(model: StPerson::class)->create(name: 'Alice', homeAddress: $home, workAddress: $work);

        $person = query(model: StPerson::class)
            ->select()
            ->with('homeAddress', 'workAddress')
            ->first();

        $this->assertSame('Alice', $person->name);
        $this->assertInstanceOf(StAddress::class, $person->homeAddress);
        $this->assertInstanceOf(StAddress::class, $person->workAddress);
        $this->assertSame('123 Home St', $person->homeAddress->street);
        $this->assertSame('456 Work Ave', $person->workAddress->street);
    }

    #[Test]
    public function parent_to_child_with_two_belongs_to_same_subchild(): void
    {
        $this->database->migrate(
            CreateMigrationsTable::class,
            CreateStAddressMigration::class,
            CreateStPersonMigration::class,
            CreateStCompanyMigration::class,
        );

        $home = query(model: StAddress::class)->create(street: '10 Home Rd');
        $work = query(model: StAddress::class)->create(street: '20 Office Blvd');
        $person = query(model: StPerson::class)->create(name: 'Bob', homeAddress: $home, workAddress: $work);
        query(model: StCompany::class)->create(name: 'Acme', ceo: $person);

        $company = query(model: StCompany::class)
            ->select()
            ->with('ceo', 'ceo.homeAddress', 'ceo.workAddress')
            ->first();

        $this->assertSame('Acme', $company->name);
        $this->assertInstanceOf(StPerson::class, $company->ceo);
        $this->assertSame('Bob', $company->ceo->name);
        $this->assertInstanceOf(StAddress::class, $company->ceo->homeAddress);
        $this->assertInstanceOf(StAddress::class, $company->ceo->workAddress);
        $this->assertSame('10 Home Rd', $company->ceo->homeAddress->street);
        $this->assertSame('20 Office Blvd', $company->ceo->workAddress->street);
    }

    // HasOne

    #[Test]
    public function two_has_one_to_same_table(): void
    {
        $this->database->migrate(
            CreateMigrationsTable::class,
            CreateStEmployeeMigration::class,
            CreateStContactMigration::class,
        );

        $alice = query(model: StEmployee::class)->create(name: 'Alice');
        query(model: StContact::class)->create(value: 'alice@work.com', workEmployee: $alice);
        query(model: StContact::class)->create(value: '555-1234', personalEmployee: $alice);

        $employee = query(model: StEmployee::class)
            ->select()
            ->with('workContact', 'personalContact')
            ->first();

        $this->assertSame('Alice', $employee->name);
        $this->assertInstanceOf(StContact::class, $employee->workContact);
        $this->assertInstanceOf(StContact::class, $employee->personalContact);
        $this->assertSame('alice@work.com', $employee->workContact->value);
        $this->assertSame('555-1234', $employee->personalContact->value);
    }
}

// Models

#[Table('st_users')]
final class StUser
{
    use IsDatabaseModel;

    /** @var \Tests\Tempest\Integration\Database\StMessage[] */
    #[HasMany(ownerJoin: 'sender_id')]
    public array $sentMessages = [];

    /** @var \Tests\Tempest\Integration\Database\StMessage[] */
    #[HasMany(ownerJoin: 'receiver_id')]
    public array $receivedMessages = [];

    public string $name;
}

#[Table('st_messages')]
final class StMessage
{
    use IsDatabaseModel;

    public string $body;

    #[BelongsTo(ownerJoin: 'sender_id')]
    public ?StUser $sender = null;

    #[BelongsTo(ownerJoin: 'receiver_id')]
    public ?StUser $receiver = null;
}

#[Table('st_addresses')]
final class StAddress
{
    use IsDatabaseModel;

    public string $street;
}

#[Table('st_persons')]
final class StPerson
{
    use IsDatabaseModel;

    #[BelongsTo(ownerJoin: 'home_address_id')]
    public ?StAddress $homeAddress = null;

    #[BelongsTo(ownerJoin: 'work_address_id')]
    public ?StAddress $workAddress = null;

    public string $name;
}

#[Table('st_companies')]
final class StCompany
{
    use IsDatabaseModel;

    #[BelongsTo(ownerJoin: 'ceo_id')]
    public ?StPerson $ceo = null;

    public string $name;
}

#[Table('st_full_spec_roles')]
final class StFullSpecRole
{
    use IsDatabaseModel;

    #[Eager]
    #[BelongsTo(relationJoin: 'st_users.id', ownerJoin: 'st_full_spec_roles.created_by')]
    public ?StUser $createdByUser = null;

    #[Eager]
    #[BelongsTo(relationJoin: 'st_users.id', ownerJoin: 'st_full_spec_roles.updated_by')]
    public ?StUser $updatedByUser = null;

    public string $code;
}

#[Table('st_roles')]
final class StRole
{
    use IsDatabaseModel;

    #[BelongsTo(ownerJoin: 'created_by')]
    public ?StUser $createdBy = null;

    #[BelongsTo(ownerJoin: 'updated_by')]
    public ?StUser $updatedBy = null;

    public string $code;
}

#[Table('st_eager_roles')]
final class StEagerRole
{
    use IsDatabaseModel;

    #[Eager]
    #[BelongsTo(ownerJoin: 'created_by')]
    public ?StUser $createdBy = null;

    #[Eager]
    #[BelongsTo(ownerJoin: 'updated_by')]
    public ?StUser $updatedBy = null;

    /** @var \Tests\Tempest\Integration\Database\StTask[] */
    #[HasMany(ownerJoin: 'role_id')]
    public array $tasks = [];

    public string $code;
}

#[Table('st_tasks')]
final class StTask
{
    use IsDatabaseModel;

    #[BelongsTo(ownerJoin: 'role_id')]
    public ?StEagerRole $role = null;

    public string $title;
}

#[Table('st_employees')]
final class StEmployee
{
    use IsDatabaseModel;

    #[HasOne(ownerJoin: 'employee_work_id')]
    public ?StContact $workContact = null;

    #[HasOne(ownerJoin: 'employee_personal_id')]
    public ?StContact $personalContact = null;

    public string $name;
}

#[Table('st_contacts')]
final class StContact
{
    use IsDatabaseModel;

    public string $value;

    #[BelongsTo(ownerJoin: 'employee_work_id')]
    public ?StEmployee $workEmployee = null;

    #[BelongsTo(ownerJoin: 'employee_personal_id')]
    public ?StEmployee $personalEmployee = null;
}

// Migrations

final class CreateStUserMigration implements MigratesUp
{
    public string $name = '001_create_st_users';

    public function up(): QueryStatement
    {
        return CreateTableStatement::forModel(modelClass: StUser::class)
            ->primary()
            ->text(name: 'name');
    }
}

final class CreateStMessageMigration implements MigratesUp
{
    public string $name = '002_create_st_messages';

    public function up(): QueryStatement
    {
        return CreateTableStatement::forModel(modelClass: StMessage::class)
            ->primary()
            ->text(name: 'body')
            ->belongsTo(local: 'st_messages.sender_id', foreign: 'st_users.id')
            ->belongsTo(local: 'st_messages.receiver_id', foreign: 'st_users.id');
    }
}

final class CreateStAddressMigration implements MigratesUp
{
    public string $name = '001_create_st_addresses';

    public function up(): QueryStatement
    {
        return CreateTableStatement::forModel(modelClass: StAddress::class)
            ->primary()
            ->text(name: 'street');
    }
}

final class CreateStPersonMigration implements MigratesUp
{
    public string $name = '002_create_st_persons';

    public function up(): QueryStatement
    {
        return CreateTableStatement::forModel(modelClass: StPerson::class)
            ->primary()
            ->text(name: 'name')
            ->belongsTo(local: 'st_persons.home_address_id', foreign: 'st_addresses.id')
            ->belongsTo(local: 'st_persons.work_address_id', foreign: 'st_addresses.id');
    }
}

final class CreateStCompanyMigration implements MigratesUp
{
    public string $name = '003_create_st_companies';

    public function up(): QueryStatement
    {
        return CreateTableStatement::forModel(modelClass: StCompany::class)
            ->primary()
            ->text(name: 'name')
            ->belongsTo(local: 'st_companies.ceo_id', foreign: 'st_persons.id');
    }
}

final class CreateStRoleMigration implements MigratesUp
{
    public string $name = '002_create_st_roles';

    public function up(): QueryStatement
    {
        return CreateTableStatement::forModel(modelClass: StRole::class)
            ->primary()
            ->text(name: 'code')
            ->belongsTo(local: 'st_roles.created_by', foreign: 'st_users.id')
            ->belongsTo(local: 'st_roles.updated_by', foreign: 'st_users.id');
    }
}

final class CreateStEagerRoleMigration implements MigratesUp
{
    public string $name = '002_create_st_eager_roles';

    public function up(): QueryStatement
    {
        return CreateTableStatement::forModel(modelClass: StEagerRole::class)
            ->primary()
            ->text(name: 'code')
            ->belongsTo(local: 'st_eager_roles.created_by', foreign: 'st_users.id')
            ->belongsTo(local: 'st_eager_roles.updated_by', foreign: 'st_users.id');
    }
}

final class CreateStTaskMigration implements MigratesUp
{
    public string $name = '003_create_st_tasks';

    public function up(): QueryStatement
    {
        return CreateTableStatement::forModel(modelClass: StTask::class)
            ->primary()
            ->text(name: 'title')
            ->belongsTo(local: 'st_tasks.role_id', foreign: 'st_eager_roles.id');
    }
}

final class CreateStContactMigration implements MigratesUp
{
    public string $name = '002_create_st_contacts';

    public function up(): QueryStatement
    {
        return CreateTableStatement::forModel(modelClass: StContact::class)
            ->primary()
            ->text(name: 'value')
            ->belongsTo(local: 'st_contacts.employee_work_id', foreign: 'st_employees.id', nullable: true)
            ->belongsTo(local: 'st_contacts.employee_personal_id', foreign: 'st_employees.id', nullable: true);
    }
}

final class CreateStEmployeeMigration implements MigratesUp
{
    public string $name = '002_create_st_employees';

    public function up(): QueryStatement
    {
        return CreateTableStatement::forModel(modelClass: StEmployee::class)
            ->primary()
            ->text(name: 'name');
    }
}

final class CreateStFullSpecRoleMigration implements MigratesUp
{
    public string $name = '002_create_st_full_spec_roles';

    public function up(): QueryStatement
    {
        return CreateTableStatement::forModel(modelClass: StFullSpecRole::class)
            ->primary()
            ->text(name: 'code')
            ->belongsTo(local: 'st_full_spec_roles.created_by', foreign: 'st_users.id')
            ->belongsTo(local: 'st_full_spec_roles.updated_by', foreign: 'st_users.id');
    }
}
