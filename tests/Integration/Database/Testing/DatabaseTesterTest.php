<?php

namespace Tests\Tempest\Integration\Database\Testing;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\Attributes\PreCondition;
use PHPUnit\Framework\Attributes\Test;
use Tempest\Database\MigratesUp;
use Tempest\Database\Migrations\CreateMigrationsTable;
use Tempest\Database\PrimaryKey;
use Tempest\Database\QueryStatement;
use Tempest\Database\QueryStatements\CreateTableStatement;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

use function Tempest\Database\query;

/**
 * @mago-expect lint:no-empty-catch-clause
 */
final class DatabaseTesterTest extends FrameworkIntegrationTestCase
{
    #[PreCondition]
    protected function configure(): void
    {
        $this->database->reset(migrate: false);
        $this->database->migrate(CreateMigrationsTable::class, CreateUsersTable::class);
    }

    #[Test]
    public function assert_has_row(): void
    {
        query(User::class)
            ->insert(name: 'Frieren', email: 'frieren@mages.org')
            ->execute();

        $this->database->assertTableHasRow(User::class, name: 'Frieren');
        $this->database->assertTableHasRow(User::class, email: 'frieren@mages.org');

        try {
            $this->database->assertTableHasRow(User::class, name: 'Eisen');
            Assert::fail('Expected an assertion failure.');
        } catch (AssertionFailedError) {
        }
    }

    #[Test]
    public function assert_count(): void
    {
        $this->database->assertTableHasCount(User::class, count: 0);

        query(User::class)
            ->insert(name: 'Frieren', email: 'frieren@mages.org')
            ->execute();

        query(User::class)
            ->insert(name: 'Eisen', email: 'eisen@mages.org')
            ->execute();

        $this->database->assertTableHasCount(User::class, count: 2);

        try {
            $this->database->assertTableHasCount(User::class, count: 3);
            Assert::fail('Expected an assertion failure.');
        } catch (AssertionFailedError) {
        }
    }

    #[Test]
    public function assert_no_row(): void
    {
        query(User::class)
            ->insert(name: 'Frieren', email: 'frieren@mages.org')
            ->execute();

        $this->database->assertTableDoesNotHaveRow(User::class, name: 'Eisen');

        query(User::class)
            ->insert(name: 'Eisen', email: 'eisen@mages.org')
            ->execute();

        try {
            $this->database->assertTableDoesNotHaveRow(User::class, name: 'Eisen');
            Assert::fail('Expected an assertion failure.');
        } catch (AssertionFailedError) {
        }
    }

    #[Test]
    public function assert_table_empty(): void
    {
        $this->database->assertTableEmpty(User::class);

        query(User::class)
            ->insert(name: 'Frieren', email: 'frieren@mages.org')
            ->execute();

        try {
            $this->database->assertTableEmpty(User::class);
            Assert::fail('Expected an assertion failure.');
        } catch (AssertionFailedError) {
        }
    }

    #[Test]
    public function assert_table_not_empty(): void
    {
        try {
            $this->database->assertTableNotEmpty(User::class);
            Assert::fail('Expected an assertion failure.');
        } catch (AssertionFailedError) {
        }

        query(User::class)
            ->insert(name: 'Frieren', email: 'frieren@mages.org')
            ->execute();

        $this->database->assertTableNotEmpty(User::class);
    }
}

final class User
{
    public PrimaryKey $id;

    public string $name;

    public string $email;
}

final class CreateUsersTable implements MigratesUp
{
    public string $name = '0-create_users_table';

    public function up(): QueryStatement
    {
        return new CreateTableStatement('users')
            ->primary()
            ->string('name')
            ->string('email');
    }
}
