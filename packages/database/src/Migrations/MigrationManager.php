<?php

declare(strict_types=1);

namespace Tempest\Database\Migrations;

use Tempest\Container\Container;
use Tempest\Database\Config\DatabaseDialect;
use Tempest\Database\Database;
use Tempest\Database\Exceptions\QueryWasInvalid;
use Tempest\Database\HasLeadingStatements;
use Tempest\Database\HasTrailingStatements;
use Tempest\Database\MigratesDown;
use Tempest\Database\MigratesUp;
use Tempest\Database\OnDatabase;
use Tempest\Database\Query;
use Tempest\Database\QueryStatement;
use Tempest\Database\QueryStatements\CompoundStatement;
use Tempest\Database\QueryStatements\DropTableStatement;
use Tempest\Database\QueryStatements\SetForeignKeyChecksStatement;
use Tempest\Database\QueryStatements\ShowTablesStatement;
use Tempest\Database\ShouldMigrate;
use Throwable;

use function Tempest\Database\inspect;
use function Tempest\Database\query;
use function Tempest\EventBus\event;

final class MigrationManager
{
    use OnDatabase;

    private Database $database {
        get => $this->container->get(Database::class, $this->onDatabase);
    }

    private DatabaseDialect $dialect {
        get => $this->database->dialect;
    }

    public function __construct(
        private readonly RunnableMigrations $migrations,
        private readonly Container $container,
    ) {}

    public function up(): void
    {
        try {
            $existingMigrations = Migration::select()->onDatabase($this->onDatabase)->all();
        } catch (QueryWasInvalid $queryWasInvalid) {
            if ($this->dialect->isTableNotFoundError($queryWasInvalid)) {
                $this->executeUp(new CreateMigrationsTable());
                $existingMigrations = Migration::select()->onDatabase($this->onDatabase)->all();
            } else {
                throw $queryWasInvalid;
            }
        }

        $existingMigrations = array_map(
            static fn (Migration $migration) => $migration->name,
            $existingMigrations,
        );

        foreach ($this->migrations->up() as $migration) {
            if (in_array($migration->name, $existingMigrations, strict: true)) {
                continue;
            }

            $this->executeUp($migration);
        }
    }

    public function down(): void
    {
        try {
            $existingMigrations = Migration::select()->onDatabase($this->onDatabase)->all();
        } catch (QueryWasInvalid $queryWasInvalid) {
            if (! $this->dialect->isTableNotFoundError($queryWasInvalid)) {
                throw $queryWasInvalid;
            }

            event(new MigrationFailed(
                name: inspect(Migration::class)->getTableDefinition()->name,
                exception: new TableWasNotFound(),
            ));

            return;
        }

        $existingMigrations = array_map(
            static fn (Migration $migration) => $migration->name,
            $existingMigrations,
        );

        foreach ($this->migrations->down() as $migration) {
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
            new SetForeignKeyChecksStatement(enable: false)->execute($this->dialect, $this->onDatabase);

            // Drop each table
            foreach ($tables as $table) {
                new DropTableStatement($table->name)->execute($this->dialect, $this->onDatabase);

                event(new TableDropped($table->name));
            }
        } catch (Throwable $throwable) {
            event(new FreshMigrationFailed($throwable));
        } finally {
            // Enable foreign key checks
            new SetForeignKeyChecksStatement(enable: true)->execute($this->dialect, $this->onDatabase);
        }
    }

    public function rehashAll(): void
    {
        try {
            $existingMigrations = Migration::select()
                ->onDatabase($this->onDatabase)
                ->all();
        } catch (QueryWasInvalid) {
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
                static fn (MigratesUp|MigratesDown $migration) => $migration->name === $existingMigration->name,
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
            $existingMigrations = Migration::select()->onDatabase($this->onDatabase)->all();
        } catch (QueryWasInvalid) {
            return;
        }

        foreach ($existingMigrations as $existingMigration) {
            $databaseMigration = array_find(
                iterator_to_array($this->migrations),
                static fn (MigratesUp|MigratesDown $migration) => $migration->name === $existingMigration->name,
            );

            if ($databaseMigration === null) {
                event(new MigrationValidationFailed($existingMigration->name, new MigrationFileWasMissing()));

                continue;
            }

            if ($this->getMigrationHash($databaseMigration) !== $existingMigration->hash) {
                event(new MigrationValidationFailed($existingMigration->name, new MigrationHashMismatched()));

                continue;
            }
        }
    }

    public function executeUp(MigratesUp $migration): void
    {
        if ($migration instanceof ShouldMigrate && $migration->shouldMigrate($this->database) === false) {
            return;
        }

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

                if (! trim($sql)) {
                    continue;
                }

                $query = new Query($sql);
                $this->database->execute($query);
            }

            $this->database->execute(
                query(Migration::class)->insert(
                    name: $migration->name,
                    hash: $this->getMigrationHash($migration),
                ),
            );
        } catch (QueryWasInvalid $queryWasInvalid) {
            event(new MigrationFailed($migration->name, $queryWasInvalid));

            throw $queryWasInvalid;
        }

        event(new MigrationMigrated($migration->name));
    }

    public function executeDown(MigratesDown $migration): void
    {
        if ($migration instanceof ShouldMigrate && $migration->shouldMigrate($this->database) === false) {
            return;
        }

        $statement = $migration->down();

        $query = new Query($statement->compile($this->dialect));

        try {
            // TODO: don't just disable FK checking when executing down

            // Disable foreign key checks
            new SetForeignKeyChecksStatement(enable: false)->execute($this->dialect, $this->onDatabase);

            $this->database->execute($query);

            // Enable foreign key checks
            new SetForeignKeyChecksStatement(enable: true)->execute($this->dialect, $this->onDatabase);
        } catch (QueryWasInvalid $queryWasInvalid) {
            // Enable foreign key checks
            new SetForeignKeyChecksStatement(enable: true)->execute($this->dialect, $this->onDatabase);

            event(new MigrationFailed($migration->name, $queryWasInvalid));

            throw $queryWasInvalid;
        }

        try {
            $this->database->execute(
                new Query(
                    'DELETE FROM Migration WHERE name = :name',
                    ['name' => $migration->name],
                ),
            );
        } catch (QueryWasInvalid) { // @mago-expect lint:no-empty-catch-clause
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

    private function getMigrationHash(MigratesUp|MigratesDown $migration): string
    {
        $sql = '';

        if ($migration instanceof MigratesUp) {
            $sql .= $this->getMinifiedSqlFromStatement($migration->up());
        }

        if ($migration instanceof MigratesDown) {
            $sql .= $this->getMinifiedSqlFromStatement($migration->down());
        }

        return hash('xxh128', $sql);
    }

    private function getMinifiedSqlFromStatement(?QueryStatement $statement): string
    {
        if ($statement === null) {
            return '';
        }

        $query = new Query($statement->compile($this->dialect));

        // Remove comments
        $sql = preg_replace('/--.*$/m', '', $query->compile()->toString()); // Remove SQL single-line comments
        $sql = preg_replace('/\/\*[\s\S]*?\*\//', '', $sql); // Remove block comments

        // Remove blank lines and excessive spaces
        $sql = preg_replace('/\s+/', ' ', trim($sql));

        return $sql;
    }
}
