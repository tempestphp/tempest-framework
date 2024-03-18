<?php

declare(strict_types=1);

namespace Tempest\Database\Migrations;

use PDOException;
use Tempest\Container\Container;
use Tempest\Database\Database;
use Tempest\Database\DatabaseConfig;
use Tempest\Database\Migration as MigrationInterface;
use function Tempest\event;
use Tempest\ORM\Operator;

final readonly class MigrationManager
{
    public function __construct(
        private Container $container,
        private DatabaseConfig $databaseConfig,
        private Database $database,
    ) {
    }

    public function up(): void
    {
        try {
            $existingMigrations = Migration::all();
        } catch (PDOException) {
            $this->executeUp(new CreateMigrationsTable());

            $existingMigrations = Migration::all();
        }

        $existingMigrations = array_map(
            fn (Migration $migration) => $migration->name,
            $existingMigrations,
        );

        foreach ($this->databaseConfig->migrations as $migrationClassName) {
            /** @var MigrationInterface $migration */
            $migration = $this->container->get($migrationClassName);

            if (in_array($migration->getName(), $existingMigrations)) {
                continue;
            }

            $this->executeUp($migration);
        }
    }

    public function down(): void
    {
        try {
            $existingMigrations = Migration::all();
        } catch (PDOException) {
            // @todo should be handled better as PDO exception doesn't necessarily mean that the migrations table doesn't exist
            return;
        }

        $existingMigrations = array_map(
            fn (Migration $migration) => $migration->name,
            $existingMigrations,
        );

        foreach ($this->databaseConfig->migrations as $migrationClassName) {
            /** @var MigrationInterface $migration */
            $migration = $this->container->get($migrationClassName);

            /**
             * If the migration is not in the existing migrations, it means it has not been executed
             */
            if (! in_array($migration->getName(), $existingMigrations)) {
                continue;
            }

            $this->executeDown($migration);
        }
    }

    public function executeUp(MigrationInterface $migration): void
    {
        $query = $migration->up();

        if (! $query) {
            return;
        }

        $this->database->execute($query);

        Migration::create(
            name: $migration->getName(),
        );

        event(new MigrationMigrated($migration->getName()));
    }

    public function executeDown(MigrationInterface $migration): void
    {
        $query = $migration->down();

        if (! $query) {
            return;
        }

        $this->database->execute($query);

        try {
            Migration::firstWhere('name', Operator::Equals, $migration->getName())
                ->delete();
        } catch (PDOException) {
            // @todo should be handled better as PDO exception doesn't necessarily mean that the migrations table doesn't exist
        }

        event(new MigrationRolledBack($migration->getName()));
    }
}
