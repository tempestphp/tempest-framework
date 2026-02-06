<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Database\QueryStatements;

use PHPUnit\Framework\Attributes\Test;
use Tempest\Database\Config\DatabaseConfig;
use Tempest\Database\Config\DatabaseDialect;
use Tempest\Database\Exceptions\QueryWasInvalid;
use Tempest\Database\MigratesUp;
use Tempest\Database\Migrations\CreateMigrationsTable;
use Tempest\Database\Migrations\Migration as MigrationModel;
use Tempest\Database\PrimaryKey;
use Tempest\Database\QueryStatement;
use Tempest\Database\QueryStatements\AlterTableStatement;
use Tempest\Database\QueryStatements\VarcharStatement;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 */
final class AlterTableStatementTest extends FrameworkIntegrationTestCase
{
    #[Test]
    public function test_it_can_alter_a_table_definition(): void
    {
        $migration = $this->getAlterTableMigration();

        $this->database->migrate(
            CreateMigrationsTable::class,
            CreateUserDatabaseMigration::class,
        );

        $this->assertCount(2, MigrationModel::all());
        $this->assertSame(
            '0000-01-01_create_users_table',
            MigrationModel::get(new PrimaryKey(2))->name,
        );

        try {
            User::create(
                name: 'Test',
                email: 'test@example.com',
            );
        } catch (QueryWasInvalid $queryWasInvalid) {
            $message = match ($this->container->get(DatabaseConfig::class)->dialect) {
                DatabaseDialect::MYSQL => "Unknown column 'email'",
                DatabaseDialect::SQLITE => 'table users has no column named email',
                DatabaseDialect::POSTGRESQL => 'column "email" of relation "users" does not exist',
            };

            $this->assertStringContainsString($message, $queryWasInvalid->getMessage());
        }

        $this->database->migrate($migration::class);
        $this->assertCount(3, MigrationModel::all());

        $this->assertSame(
            '0000-01-02_add_email_to_user_table',
            MigrationModel::get(new PrimaryKey(3))->name,
        );

        /** @var User $user */
        $user = User::create(
            name: 'Test',
            email: 'test@example.com',
        );

        $this->assertSame('test@example.com', $user->email);
    }

    private function getAlterTableMigration(): MigratesUp
    {
        return new class() implements MigratesUp {
            private(set) string $name = '0000-01-02_add_email_to_user_table';

            public function up(): QueryStatement
            {
                return AlterTableStatement::forModel(User::class)
                    ->add(new VarcharStatement('email'));
            }
        };
    }
}
