<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Database;

use PHPUnit\Framework\Attributes\Test;
use Tempest\Database\MigratesDown;
use Tempest\Database\MigratesUp;
use Tempest\Database\Migrations\CreateMigrationsTable;
use Tempest\Database\Migrations\Migration;
use Tempest\Database\Migrations\MigrationManager;
use Tempest\Database\QueryStatement;
use Tempest\Database\QueryStatements\CreateTableStatement;
use Tempest\Database\QueryStatements\DropTableStatement;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

use function Tempest\Database\query;

/**
 * @internal
 */
final class MigrationManagerTest extends FrameworkIntegrationTestCase
{
    #[Test]
    public function migration(): void
    {
        $migrationManager = $this->container->get(MigrationManager::class);

        $migrationManager->up();

        $migrations = Migration::all();
        $this->assertNotEmpty($migrations);
        $oldCount = count($migrations);

        $migrationManager->up();

        $migrations = Migration::all();
        $this->assertNotEmpty($migrations);
        $this->assertSame($oldCount, count($migrations));
    }

    #[Test]
    public function execute_down_removes_rolled_back_migration_record(): void
    {
        $migrationManager = $this->container->get(MigrationManager::class);
        $migration = new MigrationManagerTestRollbackMigration();

        $migrationManager->executeUp(new CreateMigrationsTable());
        $migrationManager->executeUp($migration);

        $this->assertSame(1, $this->countMigrationRecords($migration));

        $migrationManager->executeDown($migration);

        $this->assertSame(0, $this->countMigrationRecords($migration));
    }

    private function countMigrationRecords(MigrationManagerTestRollbackMigration $migration): int
    {
        return query(Migration::class)
            ->count()
            ->whereRaw('name = ?', $migration->name)
            ->execute();
    }
}

final class MigrationManagerTestRollbackMigration implements MigratesUp, MigratesDown
{
    public string $name = '0000-00-00_create_migration_manager_test_table';

    public function up(): QueryStatement
    {
        return new CreateTableStatement('migration_manager_test_table')->primary();
    }

    public function down(): QueryStatement
    {
        return new DropTableStatement('migration_manager_test_table');
    }
}
