<?php

declare(strict_types=1);

namespace Tempest\Database\Migrations;

use PDOException;
use Tempest\Container\Container;
use Tempest\Database\Database;
use Tempest\Database\DatabaseConfig;
use Tempest\Database\Migration as MigrationInterface;
use Tempest\Database\Query;
use function Tempest\event;

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
            event(new MigrationFailed(null, MigrationException::noTable()));

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

        try {
            $this->database->execute($query);

            Migration::create(
                name: $migration->getName(),
            );
        } catch (PDOException $e) {
            event(new MigrationFailed($migration->getName(), $e));

            throw $e;
        }

        event(new MigrationMigrated($migration->getName()));
    }

    public function executeDown(MigrationInterface $migration): void
    {
        $query = $migration->down();

        if (! $query) {
            return;
        }

        try {
            $this->database->execute($query);
        } catch (PDOException $e) {
            event(new MigrationFailed($migration->getName(), $e));

            throw $e;
        }

        try {
            $this->database->execute(
                new Query(
                    "DELETE FROM migrations WHERE name = :name",
                    ['name' => $migration->getName()],
                )
            );
        } catch (PDOException $e) {
            /**
             * If the migration was executed successfully but the entry in the migrations table could not be deleted,
             * we should not throw an exception as the migration was successfully rolled back.
             *
             * This covers the case where migration's query deleted the migration table itself
             */
        }

        event(new MigrationRolledBack($migration->getName()));
    }
}
