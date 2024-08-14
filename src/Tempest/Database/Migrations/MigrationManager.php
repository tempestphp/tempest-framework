<?php

declare(strict_types=1);

namespace Tempest\Database\Migrations;

use PDOException;
use Tempest\Container\Container;
use Tempest\Database\Database;
use Tempest\Database\DatabaseConfig;
use Tempest\Database\DatabaseDialect;
use Tempest\Database\Exceptions\QueryException;
use Tempest\Database\Migration as MigrationInterface;
use Tempest\Database\Query;
use Tempest\Database\UnsupportedDialect;
use function Tempest\event;
use function Tempest\map;
use Throwable;
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
            $this->executeUp(new CreateMigrationsTable());

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
        } catch (PDOException $pdoException) {
            /** @throw UnhandledMatchError */
            match ((string)$pdoException->getCode()) {
                $this->databaseConfig->driver()->dialect()->tableNotFoundCode() => event(
                    event: new MigrationFailed(name: 'Migration', exception: MigrationException::noTable()),
                ),
                default => throw new UnhandledMatchError($pdoException->getMessage()),
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

    public function dropAll(): void
    {
        $dialect = $this->databaseConfig->driver()->dialect();

        try {
            // Get all tables
            $tables = map((new Query(match ($dialect) {
                DatabaseDialect::MYSQL => "SHOW FULL TABLES WHERE table_type = 'BASE TABLE'",
                DatabaseDialect::SQLITE => "select type, name from sqlite_master where type = 'table' and name not like 'sqlite_%'",
                default => throw new UnsupportedDialect(),
            }))->fetch())->collection()->to(TableDefinition::class);

            // Disable foreign key checks
            (new Query(match ($dialect) {
                DatabaseDialect::MYSQL => 'SET FOREIGN_KEY_CHECKS=0',
                DatabaseDialect::SQLITE => 'PRAGMA foreign_keys = 0',
                default => throw new UnsupportedDialect(),
            }))->execute();

            // Drop each table
            foreach ($tables as $table) {
                (new Query(match ($dialect) {
                    DatabaseDialect::MYSQL, DatabaseDialect::SQLITE => sprintf('DROP TABLE IF EXISTS %s', $table->name),
                    default => throw new UnsupportedDialect(),
                }))->execute();

                event(new TableDropped($table->name));
            }
        } catch (Throwable $throwable) {
            event(new FreshMigrationFailed($throwable));
        } finally {
            // Enable foreign key checks
            (new Query(match ($dialect) {
                DatabaseDialect::MYSQL => 'SET FOREIGN_KEY_CHECKS=1',
                DatabaseDialect::SQLITE => 'PRAGMA foreign_keys = 1',
                default => throw new UnsupportedDialect(),
            }))->execute();
        }
    }

    public function executeUp(MigrationInterface $migration): void
    {
        $statement = $migration->up();

        if ($statement === null) {
            return;
        }

        $dialect = $this->databaseConfig->driver()->dialect();

        $query = new Query($statement->compile($dialect));

        try {
            $this->database->execute($query);

            Migration::create(
                name: $migration->getName(),
            );
        } catch (PDOException $pdoException) {
            event(new MigrationFailed($migration->getName(), $pdoException));

            throw $pdoException;
        }

        event(new MigrationMigrated($migration->getName()));
    }

    public function executeDown(MigrationInterface $migration): void
    {
        $statement = $migration->down();

        if ($statement === null) {
            return;
        }

        $dialect = $this->databaseConfig->driver()->dialect();

        $query = new Query($statement->compile($dialect));

        try {
            $this->database->execute($query);
        } catch (PDOException $pdoException) {
            event(new MigrationFailed($migration->getName(), $pdoException));

            throw $pdoException;
        }

        try {
            $this->database->execute(
                new Query(
                    "DELETE FROM Migration WHERE name = :name",
                    ['name' => $migration->getName()],
                ),
            );
        } catch (QueryException) {
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
