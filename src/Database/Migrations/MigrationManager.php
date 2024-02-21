<?php

declare(strict_types=1);

namespace Tempest\Database\Migrations;

use PDOException;
use Tempest\Container\Container;
use Tempest\Database\Database;
use Tempest\Database\DatabaseConfig;
use Tempest\Database\Migration as MigrationInterface;
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
}
