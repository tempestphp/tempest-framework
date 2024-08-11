<?php

declare(strict_types=1);

namespace Tempest\Database\Migrations;

use PDOException;
use Tempest\Container\Container;
use Tempest\Database\Database;
use Tempest\Database\DatabaseConfig;
use Tempest\Database\Exceptions\QueryException;
use Tempest\Database\Migration as MigrationInterface;
use Tempest\Database\Query;
use function Tempest\event;
use UnhandledMatchError;

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
            $this->executeUp(new CreateMigrationsTable($this->databaseConfig->driver()));

            $existingMigrations = Migration::all();
        }

        $existingMigrations = array_map(
            static fn (Migration $migration) => $migration->name,
            $existingMigrations,
        );

        $migrations = $this->databaseConfig->getMigrations();
        ksort($migrations);
        foreach ($migrations as $migrationClassName) {
            /** @var MigrationInterface $migration */
            $migration = $this->container->get($migrationClassName, driver: $this->databaseConfig->driver());

            if (in_array($migration->getName(), $existingMigrations, strict: true)) {
                continue;
            }

            $this->executeUp($migration);
        }
    }

    public function down(): void
    {
        try {
            $existingMigrations = Migration::all();
        } catch (PDOException $exception) {
            /** @throw UnhandledMatchError */
            match ((string) $exception->getCode()) {
                $this->databaseConfig->driver()->dialect()->tableNotFoundCode() => event(
                    event: new MigrationFailed(name: 'Migration', exception: MigrationException::noTable())
                ),
                default => throw new UnhandledMatchError($exception->getMessage()),
            };

            return;
        }

        $existingMigrations = array_map(
            static fn (Migration $migration) => $migration->name,
            $existingMigrations,
        );

        $migrations = $this->databaseConfig->getMigrations();
        krsort($migrations);
        foreach ($migrations as $migrationClassName) {
            /** @var MigrationInterface $migration */
            $migration = $this->container->get($migrationClassName);

            /* If the migration is not in the existing migrations, it means it has not been executed */
            if (! in_array($migration->getName(), $existingMigrations, strict: true)) {
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
                    "DELETE FROM Migration WHERE name = :name",
                    ['name' => $migration->getName()],
                )
            );
        } catch (QueryException $e) {
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
