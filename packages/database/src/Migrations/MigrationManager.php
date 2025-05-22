<?php

declare(strict_types=1);

namespace Tempest\Database\Migrations;

use PDOException;
use Tempest\Database\Builder\ModelDefinition;
use Tempest\Database\Config\DatabaseDialect;
use Tempest\Database\Database;
use Tempest\Database\DatabaseMigration as MigrationInterface;
use Tempest\Database\DatabaseMigration;
use Tempest\Database\Exceptions\QueryException;
use Tempest\Database\HasLeadingStatements;
use Tempest\Database\HasTrailingStatements;
use Tempest\Database\Query;
use Tempest\Database\QueryStatement;
use Tempest\Database\QueryStatements\CompoundStatement;
use Tempest\Database\QueryStatements\DropTableStatement;
use Tempest\Database\QueryStatements\SetForeignKeyChecksStatement;
use Tempest\Database\QueryStatements\ShowTablesStatement;
use Throwable;

use function Tempest\event;

final readonly class MigrationManager
{
    public function __construct(
        private DatabaseDialect $dialect,
        private Database $database,
        private RunnableMigrations $migrations,
    ) {}

    public function up(): void
    {
        try {
            $existingMigrations = Migration::all();
        } catch (PDOException $pdoException) {
            if ($this->dialect->isTableNotFoundError($pdoException)) {
                $this->executeUp(new CreateMigrationsTable());
                $existingMigrations = Migration::all();
            } else {
                throw $pdoException;
            }
        }

        $existingMigrations = array_map(
            static fn (Migration $migration) => $migration->name,
            $existingMigrations,
        );

        foreach ($this->migrations as $migration) {
            if (in_array($migration->name, $existingMigrations, strict: true)) {
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
            if (! $this->dialect->isTableNotFoundError($pdoException)) {
                throw $pdoException;
            }

            event(new MigrationFailed(
                name: new ModelDefinition(Migration::class)->getTableDefinition()->name,
                exception: new TableNotFoundException(),
            ));

            return;
        }

        $existingMigrations = array_map(
            static fn (Migration $migration) => $migration->name,
            $existingMigrations,
        );

        foreach ($this->migrations as $migration) {
            /* If the migration is not in the existing migrations, it means it has not been executed */
            if (! in_array($migration->name, $existingMigrations, strict: true)) {
                continue;
            }

            $this->executeDown($migration);
        }
    }

    public function dropAll(): void
    {
        try {
            // Get all tables
            $tables = $this->getTableDefinitions();

            // Disable foreign key checks
            new SetForeignKeyChecksStatement(enable: false)->execute($this->dialect);

            // Drop each table
            foreach ($tables as $table) {
                new DropTableStatement($table->name)->execute($this->dialect);

                event(new TableDropped($table->name));
            }
        } catch (Throwable $throwable) {
            event(new FreshMigrationFailed($throwable));
        } finally {
            // Enable foreign key checks
            new SetForeignKeyChecksStatement(enable: true)->execute($this->dialect);
        }
    }

    public function rehashAll(): void
    {
        try {
            $existingMigrations = Migration::all();
        } catch (PDOException) {
            return;
        }

        foreach ($existingMigrations as $existingMigration) {
            /**
             * We need to find and delete migration DB records that no longer have a corresponding migration file.
             * This can happen if a migration file was deleted or renamed.
             * If we don't do it, `:validate` will continue failing due to the missing migration file.
             */
            $databaseMigration = array_find(
                iterator_to_array($this->migrations),
                static fn (DatabaseMigration $migration) => $migration->name === $existingMigration->name,
            );

            if ($databaseMigration === null) {
                $existingMigration->delete();

                continue;
            }

            $existingMigration->update(
                hash: $this->getMigrationHash($databaseMigration),
            );
        }
    }

    public function validate(): void
    {
        try {
            $existingMigrations = Migration::all();
        } catch (PDOException) {
            return;
        }

        foreach ($existingMigrations as $existingMigration) {
            $databaseMigration = array_find(
                iterator_to_array($this->migrations),
                static fn (DatabaseMigration $migration) => $migration->name === $existingMigration->name,
            );

            if ($databaseMigration === null) {
                event(new MigrationValidationFailed($existingMigration->name, new MissingMigrationFileException()));

                continue;
            }

            if ($this->getMigrationHash($databaseMigration) !== $existingMigration->hash) {
                event(new MigrationValidationFailed($existingMigration->name, new MigrationHashMismatchException()));

                continue;
            }
        }
    }

    public function executeUp(MigrationInterface $migration): void
    {
        $statement = $migration->up();

        if ($statement === null) {
            return;
        }

        if ($statement instanceof CompoundStatement) {
            $statements = $statement->statements;
        } else {
             $statements = [$statement];
        }

        if ($statement instanceof HasLeadingStatements) {
            $statements = [...$statement->leadingStatements, ...$statements];
        }

        if ($statement instanceof HasTrailingStatements) {
            $statements = [...$statements, ...$statement->trailingStatements];
        }

        try {
            foreach ($statements as $statement) {
                $sql = $statement->compile($this->dialect);
                $query = new Query($sql);
                $this->database->execute($query);
            }

            Migration::create(
                name: $migration->name,
                hash: $this->getMigrationHash($migration),
            );
        } catch (PDOException $pdoException) {
            event(new MigrationFailed($migration->name, $pdoException));

            throw $pdoException;
        }

        event(new MigrationMigrated($migration->name));
    }

    public function executeDown(MigrationInterface $migration): void
    {
        $statement = $migration->down();

        if ($statement === null) {
            return;
        }

        $query = new Query($statement->compile($this->dialect));

        try {
            // TODO: don't just disable FK checking when executing down

            // Disable foreign key checks
            new SetForeignKeyChecksStatement(enable: false)->execute($this->dialect);

            $this->database->execute($query);

            // Disable foreign key checks
            new SetForeignKeyChecksStatement(enable: true)->execute($this->dialect);
        } catch (PDOException $pdoException) {
            // Disable foreign key checks
            new SetForeignKeyChecksStatement(enable: true)->execute($this->dialect);

            event(new MigrationFailed($migration->name, $pdoException));

            throw $pdoException;
        }

        try {
            $this->database->execute(
                new Query(
                    'DELETE FROM Migration WHERE name = :name',
                    ['name' => $migration->name],
                ),
            );
        } catch (QueryException) { // @mago-expect best-practices/no-empty-catch-clause
            /**
             * If the migration was executed successfully but the entry in the migrations table could not be deleted,
             * we should not throw an exception as the migration was successfully rolled back.
             *
             * This covers the case where migration's query deleted the migration table itself
             */
        }

        event(new MigrationRolledBack($migration->name));
    }

    /**
     * @return \Tempest\Database\Migrations\TableMigrationDefinition[]
     */
    private function getTableDefinitions(): array
    {
        return array_map(
            fn (array $item) => match ($this->dialect) {
                DatabaseDialect::SQLITE => new TableMigrationDefinition($item['name']),
                DatabaseDialect::POSTGRESQL => new TableMigrationDefinition($item['table_name']),
                DatabaseDialect::MYSQL => new TableMigrationDefinition(array_values($item)[0]),
            },
            new ShowTablesStatement()->fetch($this->dialect),
        );
    }

    private function getMigrationHash(DatabaseMigration $migration): string
    {
        $minifiedDownSql = $this->getMinifiedSqlFromStatement($migration->down());
        $minifiedUpSql = $this->getMinifiedSqlFromStatement($migration->up());

        return hash('xxh128', $minifiedDownSql . $minifiedUpSql);
    }

    private function getMinifiedSqlFromStatement(?QueryStatement $statement): string
    {
        if ($statement === null) {
            return '';
        }

        $query = new Query($statement->compile($this->dialect));

        // Remove comments
        $sql = preg_replace('/--.*$/m', '', $query->toSql()); // Remove SQL single-line comments
        $sql = preg_replace('/\/\*[\s\S]*?\*\//', '', $sql); // Remove block comments

        // Remove blank lines and excessive spaces
        $sql = preg_replace('/\s+/', ' ', trim($sql));

        return $sql;
    }
}
