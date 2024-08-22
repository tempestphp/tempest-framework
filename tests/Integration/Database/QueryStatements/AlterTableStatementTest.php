<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Database\QueryStatements;

use PHPUnit\Framework\Attributes\Test;
use Tempest\Database\Exceptions\QueryException;
use Tempest\Database\Id;
use Tempest\Database\Migration;
use Tempest\Database\Migrations\CreateMigrationsTable;
use Tempest\Database\Migrations\Migration as MigrationModel;
use Tempest\Database\QueryStatement;
use Tempest\Database\QueryStatements\AlterTableStatement;
use Tempest\Database\QueryStatements\VarcharStatement;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 * @small
 */
final class AlterTableStatementTest extends FrameworkIntegrationTestCase
{
    #[Test]
    public function it_can_alter_a_table_definition(): void
    {
        $migration = $this->getAlterTableMigration();

        $this->migrate(
            CreateMigrationsTable::class,
            CreateUserMigration::class,
        );

        $this->assertCount(2, MigrationModel::all());
        $this->assertSame(
            '0000-01-01_create_users_table',
            MigrationModel::find(new Id(2))->name
        );

        try {
            User::create(
                name: 'Test',
                email: 'test@example.com'
            );
        } catch (QueryException $exception) {
            $this->assertStringContainsString('table User has no column named email', $exception->getMessage());
        }

        $this->migrate($migration::class);
        $this->assertCount(3, MigrationModel::all());

        $this->assertSame(
            '0000-01-02_add_email_to_user_table',
            MigrationModel::find(new Id(3))->name
        );

        /** @var User $user */
        $user = User::create(
            name: 'Test',
            email: 'test@example.com'
        );

        $this->assertSame('test@example.com', $user->email);
    }

    private function getAlterTableMigration(): mixed
    {
        return new class () implements Migration {
            public function getName(): string
            {
                return '0000-01-02_add_email_to_user_table';
            }

            public function up(): QueryStatement|null
            {
                return (new AlterTableStatement('User'))
                    ->add(new VarcharStatement('email'));
            }

            public function down(): QueryStatement|null
            {
                return null;
            }
        };
    }
}