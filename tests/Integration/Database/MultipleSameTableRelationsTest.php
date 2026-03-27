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
            CreateSameTableTestUserMigration::class,
            CreateSameTableTestRoleMigration::class,
        );

        $alice = query(model: SameTableTestUser::class)->create(name: 'Alice');
        $bob = query(model: SameTableTestUser::class)->create(name: 'Bob');
        query(model: SameTableTestRole::class)->create(code: 'admin', createdBy: $alice, updatedBy: $bob);

        $role = query(model: SameTableTestRole::class)
            ->select()
            ->with('createdBy', 'updatedBy')
            ->first();

        $this->assertSame('admin', $role->code);
        $this->assertInstanceOf(SameTableTestUser::class, $role->createdBy);
        $this->assertInstanceOf(SameTableTestUser::class, $role->updatedBy);
        $this->assertSame('Alice', $role->createdBy->name);
        $this->assertSame('Bob', $role->updatedBy->name);
    }

    #[Test]
    public function two_belongs_to_same_table_with_full_table_column_syntax(): void
    {
        $this->database->migrate(
            CreateMigrationsTable::class,
            CreateSameTableTestUserMigration::class,
            CreateSameTableTestFullSpecRoleMigration::class,
        );

        $alice = query(model: SameTableTestUser::class)->create(name: 'Alice');
        $bob = query(model: SameTableTestUser::class)->create(name: 'Bob');
        query(model: SameTableTestFullSpecRole::class)->create(code: 'moderator', createdByUser: $alice, updatedByUser: $bob);

        $role = query(model: SameTableTestFullSpecRole::class)
            ->select()
            ->with('createdByUser', 'updatedByUser')
            ->first();

        $this->assertSame('moderator', $role->code);
        $this->assertInstanceOf(SameTableTestUser::class, $role->createdByUser);
        $this->assertInstanceOf(SameTableTestUser::class, $role->updatedByUser);
        $this->assertSame('Alice', $role->createdByUser->name);
        $this->assertSame('Bob', $role->updatedByUser->name);
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
        query(model: SameTableTestEagerRole::class)->create(code: 'player', createdBy: $alice, updatedBy: $bob);

        $role = query(model: SameTableTestEagerRole::class)
            ->select()
            ->first();

        $this->assertSame('player', $role->code);
        $this->assertInstanceOf(SameTableTestUser::class, $role->createdBy);
        $this->assertInstanceOf(SameTableTestUser::class, $role->updatedBy);
        $this->assertSame('Alice', $role->createdBy->name);
        $this->assertSame('Bob', $role->updatedBy->name);
    }

    #[Test]
    public function parent_to_child_with_two_eager_belongs_to_same_table(): void
    {
        $this->database->migrate(
            CreateMigrationsTable::class,
            CreateSameTableTestUserMigration::class,
            CreateSameTableTestEagerRoleMigration::class,
            CreateSameTableTestTaskMigration::class,
        );

        $alice = query(model: SameTableTestUser::class)->create(name: 'Alice');
        $bob = query(model: SameTableTestUser::class)->create(name: 'Bob');
        $role = query(model: SameTableTestEagerRole::class)->create(code: 'admin', createdBy: $alice, updatedBy: $bob);
        query(model: SameTableTestTask::class)->create(title: 'Task 1', role: $role);

        $task = query(model: SameTableTestTask::class)
            ->select()
            ->with('role', 'role.createdBy', 'role.updatedBy')
            ->first();

        $this->assertSame('Task 1', $task->title);
        $this->assertInstanceOf(SameTableTestEagerRole::class, $task->role);
        $this->assertSame('admin', $task->role->code);
        $this->assertInstanceOf(SameTableTestUser::class, $task->role->createdBy);
        $this->assertInstanceOf(SameTableTestUser::class, $task->role->updatedBy);
        $this->assertSame('Alice', $task->role->createdBy->name);
        $this->assertSame('Bob', $task->role->updatedBy->name);
    }

    // HasMany

    #[Test]
    public function two_has_many_to_same_table(): void
    {
        $this->database->migrate(
            CreateMigrationsTable::class,
            CreateSameTableTestUserMigration::class,
            CreateSameTableTestMessageMigration::class,
        );

        $alice = query(model: SameTableTestUser::class)->create(name: 'Alice');
        $bob = query(model: SameTableTestUser::class)->create(name: 'Bob');
        query(model: SameTableTestMessage::class)->create(body: 'Hello Bob', sender: $alice, receiver: $bob);
        query(model: SameTableTestMessage::class)->create(body: 'Hi Alice', sender: $bob, receiver: $alice);

        $alice = query(model: SameTableTestUser::class)
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
            CreateSameTableTestAddressMigration::class,
            CreateSameTableTestPersonMigration::class,
        );

        $home = query(model: SameTableTestAddress::class)->create(street: '123 Home St');
        $work = query(model: SameTableTestAddress::class)->create(street: '456 Work Ave');
        query(model: SameTableTestPerson::class)->create(name: 'Alice', homeAddress: $home, workAddress: $work);

        $person = query(model: SameTableTestPerson::class)
            ->select()
            ->with('homeAddress', 'workAddress')
            ->first();

        $this->assertSame('Alice', $person->name);
        $this->assertInstanceOf(SameTableTestAddress::class, $person->homeAddress);
        $this->assertInstanceOf(SameTableTestAddress::class, $person->workAddress);
        $this->assertSame('123 Home St', $person->homeAddress->street);
        $this->assertSame('456 Work Ave', $person->workAddress->street);
    }

    #[Test]
    public function parent_to_child_with_two_belongs_to_same_subchild(): void
    {
        $this->database->migrate(
            CreateMigrationsTable::class,
            CreateSameTableTestAddressMigration::class,
            CreateSameTableTestPersonMigration::class,
            CreateSameTableTestCompanyMigration::class,
        );

        $home = query(model: SameTableTestAddress::class)->create(street: '10 Home Rd');
        $work = query(model: SameTableTestAddress::class)->create(street: '20 Office Blvd');
        $person = query(model: SameTableTestPerson::class)->create(name: 'Bob', homeAddress: $home, workAddress: $work);
        query(model: SameTableTestCompany::class)->create(name: 'Acme', ceo: $person);

        $company = query(model: SameTableTestCompany::class)
            ->select()
            ->with('ceo', 'ceo.homeAddress', 'ceo.workAddress')
            ->first();

        $this->assertSame('Acme', $company->name);
        $this->assertInstanceOf(SameTableTestPerson::class, $company->ceo);
        $this->assertSame('Bob', $company->ceo->name);
        $this->assertInstanceOf(SameTableTestAddress::class, $company->ceo->homeAddress);
        $this->assertInstanceOf(SameTableTestAddress::class, $company->ceo->workAddress);
        $this->assertSame('10 Home Rd', $company->ceo->homeAddress->street);
        $this->assertSame('20 Office Blvd', $company->ceo->workAddress->street);
    }

    // HasOne

    #[Test]
    public function two_has_one_to_same_table(): void
    {
        $this->database->migrate(
            CreateMigrationsTable::class,
            CreateSameTableTestEmployeeMigration::class,
            CreateSameTableTestContactMigration::class,
        );

        $alice = query(model: SameTableTestEmployee::class)->create(name: 'Alice');
        query(model: SameTableTestContact::class)->create(value: 'alice@work.com', workEmployee: $alice);
        query(model: SameTableTestContact::class)->create(value: '555-1234', personalEmployee: $alice);

        $employee = query(model: SameTableTestEmployee::class)
            ->select()
            ->with('workContact', 'personalContact')
            ->first();

        $this->assertSame('Alice', $employee->name);
        $this->assertInstanceOf(SameTableTestContact::class, $employee->workContact);
        $this->assertInstanceOf(SameTableTestContact::class, $employee->personalContact);
        $this->assertSame('alice@work.com', $employee->workContact->value);
        $this->assertSame('555-1234', $employee->personalContact->value);
    }
}

// Models

#[Table('same_table_test_users')]
final class SameTableTestUser
{
    use IsDatabaseModel;

    /** @var \Tests\Tempest\Integration\Database\SameTableTestMessage[] */
    #[HasMany(ownerJoin: 'sender_id')]
    public array $sentMessages = [];

    /** @var \Tests\Tempest\Integration\Database\SameTableTestMessage[] */
    #[HasMany(ownerJoin: 'receiver_id')]
    public array $receivedMessages = [];

    public string $name;
}

#[Table('same_table_test_messages')]
final class SameTableTestMessage
{
    use IsDatabaseModel;

    public string $body;

    #[BelongsTo(ownerJoin: 'sender_id')]
    public ?SameTableTestUser $sender = null;

    #[BelongsTo(ownerJoin: 'receiver_id')]
    public ?SameTableTestUser $receiver = null;
}

#[Table('same_table_test_addresses')]
final class SameTableTestAddress
{
    use IsDatabaseModel;

    public string $street;
}

#[Table('same_table_test_persons')]
final class SameTableTestPerson
{
    use IsDatabaseModel;

    #[BelongsTo(ownerJoin: 'home_address_id')]
    public ?SameTableTestAddress $homeAddress = null;

    #[BelongsTo(ownerJoin: 'work_address_id')]
    public ?SameTableTestAddress $workAddress = null;

    public string $name;
}

#[Table('same_table_test_companies')]
final class SameTableTestCompany
{
    use IsDatabaseModel;

    #[BelongsTo(ownerJoin: 'ceo_id')]
    public ?SameTableTestPerson $ceo = null;

    public string $name;
}

#[Table('same_table_test_full_spec_roles')]
final class SameTableTestFullSpecRole
{
    use IsDatabaseModel;

    #[Eager]
    #[BelongsTo(relationJoin: 'same_table_test_users.id', ownerJoin: 'same_table_test_full_spec_roles.created_by')]
    public ?SameTableTestUser $createdByUser = null;

    #[Eager]
    #[BelongsTo(relationJoin: 'same_table_test_users.id', ownerJoin: 'same_table_test_full_spec_roles.updated_by')]
    public ?SameTableTestUser $updatedByUser = null;

    public string $code;
}

#[Table('same_table_test_roles')]
final class SameTableTestRole
{
    use IsDatabaseModel;

    #[BelongsTo(ownerJoin: 'created_by')]
    public ?SameTableTestUser $createdBy = null;

    #[BelongsTo(ownerJoin: 'updated_by')]
    public ?SameTableTestUser $updatedBy = null;

    public string $code;
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

    public string $code;
}

#[Table('same_table_test_tasks')]
final class SameTableTestTask
{
    use IsDatabaseModel;

    #[BelongsTo(ownerJoin: 'role_id')]
    public ?SameTableTestEagerRole $role = null;

    public string $title;
}

#[Table('same_table_test_employees')]
final class SameTableTestEmployee
{
    use IsDatabaseModel;

    #[HasOne(ownerJoin: 'employee_work_id')]
    public ?SameTableTestContact $workContact = null;

    #[HasOne(ownerJoin: 'employee_personal_id')]
    public ?SameTableTestContact $personalContact = null;

    public string $name;
}

#[Table('same_table_test_contacts')]
final class SameTableTestContact
{
    use IsDatabaseModel;

    public string $value;

    #[BelongsTo(ownerJoin: 'employee_work_id')]
    public ?SameTableTestEmployee $workEmployee = null;

    #[BelongsTo(ownerJoin: 'employee_personal_id')]
    public ?SameTableTestEmployee $personalEmployee = null;
}

// Migrations

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

final class CreateSameTableTestMessageMigration implements MigratesUp
{
    public string $name = '002_create_same_table_test_messages';

    public function up(): QueryStatement
    {
        return CreateTableStatement::forModel(modelClass: SameTableTestMessage::class)
            ->primary()
            ->text(name: 'body')
            ->belongsTo(local: 'same_table_test_messages.sender_id', foreign: 'same_table_test_users.id')
            ->belongsTo(local: 'same_table_test_messages.receiver_id', foreign: 'same_table_test_users.id');
    }
}

final class CreateSameTableTestAddressMigration implements MigratesUp
{
    public string $name = '001_create_same_table_test_addresses';

    public function up(): QueryStatement
    {
        return CreateTableStatement::forModel(modelClass: SameTableTestAddress::class)
            ->primary()
            ->text(name: 'street');
    }
}

final class CreateSameTableTestPersonMigration implements MigratesUp
{
    public string $name = '002_create_same_table_test_persons';

    public function up(): QueryStatement
    {
        return CreateTableStatement::forModel(modelClass: SameTableTestPerson::class)
            ->primary()
            ->text(name: 'name')
            ->belongsTo(local: 'same_table_test_persons.home_address_id', foreign: 'same_table_test_addresses.id')
            ->belongsTo(local: 'same_table_test_persons.work_address_id', foreign: 'same_table_test_addresses.id');
    }
}

final class CreateSameTableTestCompanyMigration implements MigratesUp
{
    public string $name = '003_create_same_table_test_companies';

    public function up(): QueryStatement
    {
        return CreateTableStatement::forModel(modelClass: SameTableTestCompany::class)
            ->primary()
            ->text(name: 'name')
            ->belongsTo(local: 'same_table_test_companies.ceo_id', foreign: 'same_table_test_persons.id');
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
            ->belongsTo(local: 'same_table_test_roles.created_by', foreign: 'same_table_test_users.id')
            ->belongsTo(local: 'same_table_test_roles.updated_by', foreign: 'same_table_test_users.id');
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
            ->belongsTo(local: 'same_table_test_eager_roles.created_by', foreign: 'same_table_test_users.id')
            ->belongsTo(local: 'same_table_test_eager_roles.updated_by', foreign: 'same_table_test_users.id');
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
            ->belongsTo(local: 'same_table_test_tasks.role_id', foreign: 'same_table_test_eager_roles.id');
    }
}

final class CreateSameTableTestContactMigration implements MigratesUp
{
    public string $name = '002_create_same_table_test_contacts';

    public function up(): QueryStatement
    {
        return CreateTableStatement::forModel(modelClass: SameTableTestContact::class)
            ->primary()
            ->text(name: 'value')
            ->belongsTo(local: 'same_table_test_contacts.employee_work_id', foreign: 'same_table_test_employees.id', nullable: true)
            ->belongsTo(local: 'same_table_test_contacts.employee_personal_id', foreign: 'same_table_test_employees.id', nullable: true);
    }
}

final class CreateSameTableTestEmployeeMigration implements MigratesUp
{
    public string $name = '002_create_same_table_test_employees';

    public function up(): QueryStatement
    {
        return CreateTableStatement::forModel(modelClass: SameTableTestEmployee::class)
            ->primary()
            ->text(name: 'name');
    }
}

final class CreateSameTableTestFullSpecRoleMigration implements MigratesUp
{
    public string $name = '002_create_same_table_test_full_spec_roles';

    public function up(): QueryStatement
    {
        return CreateTableStatement::forModel(modelClass: SameTableTestFullSpecRole::class)
            ->primary()
            ->text(name: 'code')
            ->belongsTo(local: 'same_table_test_full_spec_roles.created_by', foreign: 'same_table_test_users.id')
            ->belongsTo(local: 'same_table_test_full_spec_roles.updated_by', foreign: 'same_table_test_users.id');
    }
}
