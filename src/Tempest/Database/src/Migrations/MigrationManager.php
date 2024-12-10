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
use Tempest\Database\QueryStatements\DropTableStatement;
use Tempest\Database\QueryStatements\SetForeignKeyChecksStatement;
use Tempest\Database\QueryStatements\ShowTablesStatement;
use Throwable;
use UnhandledMatchError;
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
            static fn (Migration $migration) => $migration->name,
            $existingMigrations,
        );

        $migrations = $this->getSortedMigrations();

        foreach ($migrations as $migration) {
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
            match ((string) $pdoException->getCode()) {
                $this->databaseConfig->connection()->dialect()->tableNotFoundCode() => event(
                    event: new MigrationFailed(name: Migration::table()->tableName, exception: MigrationException::noTable()),
                ),
                default => throw new UnhandledMatchError($pdoException->getMessage()),
            };

            return;
        }

        $existingMigrations = array_map(
            static fn (Migration $migration) => $migration->name,
            $existingMigrations,
        );

        $migrations = $this->getSortedMigrations();

        foreach ($migrations as $migration) {
            /* If the migration is not in the existing migrations, it means it has not been executed */
            if (! in_array($migration->getName(), $existingMigrations, strict: true)) {
                continue;
            }

            $this->executeDown($migration);
        }
    }

    public function dropAll(): void
    {
        $dialect = $this->databaseConfig->connection()->dialect();

        try {
            // Get all tables
            $tables = $this->getTableDefinitions($dialect);

            // Disable foreign key checks
            (new SetForeignKeyChecksStatement(enable: false))->execute($dialect);

            // Drop each table
            foreach ($tables as $table) {
                (new DropTableStatement($table->name))->execute($dialect);

                event(new TableDropped($table->name));
            }
        } catch (Throwable $throwable) {
            event(new FreshMigrationFailed($throwable));
        } finally {
            // Enable foreign key checks
            (new SetForeignKeyChecksStatement(enable: true))->execute($dialect);
        }
    }

    public function executeUp(MigrationInterface $migration): void
    {
        $statement = $migration->up();

        if ($statement === null) {
            return;
        }

        $dialect = $this->databaseConfig->connection()->dialect();

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

        $dialect = $this->databaseConfig->connection()->dialect();

        $query = new Query($statement->compile($dialect));

        try {
            // TODO: don't just disable FK checking when executing down

            // Disable foreign key checks
            (new SetForeignKeyChecksStatement(enable: false))->execute($dialect);

            $this->database->execute($query);

            // Disable foreign key checks
            (new SetForeignKeyChecksStatement(enable: true))->execute($dialect);
        } catch (PDOException $pdoException) {
            // Disable foreign key checks
            (new SetForeignKeyChecksStatement(enable: true))->execute($dialect);

            event(new MigrationFailed($migration->getName(), $pdoException));

            throw $pdoException;
        }

        try {
            $this->database->execute(
                new Query(
                    'DELETE FROM Migration WHERE name = :name',
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

    /**
     * @return MigrationInterface[]
     */
    private function getSortedMigrations(): array
    {
        /** @var MigrationInterface[] $migrations */
        $migrations = array_map(
            fn (string|MigrationInterface $migration) => is_string($migration) ? $this->container->get($migration) : $migration,
            $this->databaseConfig->getMigrations(),
        );

        usort($migrations, fn (MigrationInterface $a, MigrationInterface $b) => $a->getName() <=> $b->getName());

        return $migrations;
    }

    /**
     * @return \Tempest\Database\Migrations\TableDefinition[]
     */
    private function getTableDefinitions(DatabaseDialect $dialect): array
    {
        return array_map(
            fn (array $item) => match ($dialect) {
                DatabaseDialect::SQLITE => new TableDefinition($item['name']),
                default => new TableDefinition(array_values($item)[0]),
            },
            (new ShowTablesStatement())->fetch($dialect),
        );
    }
}
