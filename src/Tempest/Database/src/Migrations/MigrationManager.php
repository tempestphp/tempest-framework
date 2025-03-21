<?php

declare(strict_types=1);

namespace Tempest\Database\Migrations;

use PDOException;
use Tempest\Container\Container;
use Tempest\Database\Config\DatabaseConfig;
use Tempest\Database\Config\DatabaseDialect;
use Tempest\Database\Database;
use Tempest\Database\DatabaseMigration as MigrationInterface;
use Tempest\Database\DatabaseMigration;
use Tempest\Database\Exceptions\QueryException;
use Tempest\Database\Query;
use Tempest\Database\QueryStatement;
use Tempest\Database\QueryStatements\DropTableStatement;
use Tempest\Database\QueryStatements\SetForeignKeyChecksStatement;
use Tempest\Database\QueryStatements\ShowTablesStatement;
use Throwable;
use UnhandledMatchError;

use function Tempest\event;

final readonly class MigrationManager
{
    public function __construct(
        private DatabaseConfig $databaseConfig,
        private Database $database,
        private RunnableMigrations $migrations,
    ) {}

    public function up(bool $allowChanges): void
    {
        try {
            $existingMigrations = Migration::all();
        } catch (PDOException) {
            $this->executeUp(new CreateMigrationsTable());

            $existingMigrations = Migration::all();
        }

        foreach ($this->migrations as $migration) {
            $existingMigration = array_find(
                $existingMigrations,
                static fn (Migration $existingMigration) => $existingMigration->name === $migration->name,
            );

            if ($existingMigration !== null) {
                if (! $allowChanges) {
                    $this->verifyMigrationHash($migration, $existingMigration);
                }

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
                $this->databaseConfig->dialect->tableNotFoundCode() => event(
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
        $dialect = $this->databaseConfig->dialect;

        try {
            // Get all tables
            $tables = $this->getTableDefinitions($dialect);

            // Disable foreign key checks
            new SetForeignKeyChecksStatement(enable: false)->execute($dialect);

            // Drop each table
            foreach ($tables as $table) {
                new DropTableStatement($table->name)->execute($dialect);

                event(new TableDropped($table->name));
            }
        } catch (Throwable $throwable) {
            event(new FreshMigrationFailed($throwable));
        } finally {
            // Enable foreign key checks
            new SetForeignKeyChecksStatement(enable: true)->execute($dialect);
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

    public function executeUp(MigrationInterface $migration): void
    {
        $statement = $migration->up();

        if ($statement === null) {
            return;
        }

        $dialect = $this->databaseConfig->dialect;

        $query = new Query($statement->compile($dialect));

        try {
            $this->database->execute($query);

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

        $dialect = $this->databaseConfig->dialect;

        $query = new Query($statement->compile($dialect));

        try {
            // TODO: don't just disable FK checking when executing down

            // Disable foreign key checks
            new SetForeignKeyChecksStatement(enable: false)->execute($dialect);

            $this->database->execute($query);

            // Disable foreign key checks
            new SetForeignKeyChecksStatement(enable: true)->execute($dialect);
        } catch (PDOException $pdoException) {
            // Disable foreign key checks
            new SetForeignKeyChecksStatement(enable: true)->execute($dialect);

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
     * @return \Tempest\Database\Migrations\TableDefinition[]
     */
    private function getTableDefinitions(DatabaseDialect $dialect): array
    {
        return array_map(
            fn (array $item) => match ($dialect) {
                DatabaseDialect::SQLITE => new TableDefinition($item['name']),
                default => new TableDefinition(array_values($item)[0]),
            },
            new ShowTablesStatement()->fetch($dialect),
        );
    }

    private function getMigrationHash(DatabaseMigration $migration): string
    {
        $minifiedDownSql = $this->getMinifiedSqlFromStatement($migration->down());
        $minifiedUpSql = $this->getMinifiedSqlFromStatement($migration->up());

        return hash('sha256', $minifiedDownSql . $minifiedUpSql);
    }

    private function getMinifiedSqlFromStatement(?QueryStatement $statement): string
    {
        if ($statement === null) {
            return '';
        }

        $query = new Query($statement->compile($this->databaseConfig->dialect));

        // Remove comments
        $sql = preg_replace('/--.*$/m', '', $query->getSql()); // Remove SQL single-line comments
        $sql = preg_replace('/\/\*[\s\S]*?\*\//', '', $sql); // Remove block comments

        // Remove blank lines and excessive spaces
        $sql = preg_replace('/\s+/', ' ', trim($sql));

        return $sql;
    }

    private function verifyMigrationHash(DatabaseMigration $migration, Migration $existingMigration): void
    {
        $hash = $this->getMigrationHash($migration);

        if ($hash !== $existingMigration->hash) {
            event(new MigrationFailed($migration->name, MigrationException::hashMismatch()));
        }
    }
}
