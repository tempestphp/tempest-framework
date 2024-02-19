<?php

declare(strict_types=1);

namespace Tempest\Database\Migrations;

use PDO;
use PDOException;
use Tempest\Database\Builder\TableBuilder;
use Tempest\Database\DatabaseConfig;
use function Tempest\event;
use Tempest\Interface\Container;
use Tempest\Interface\Migration as MigrationInterface;

final readonly class MigrationManager
{
    public function __construct(
        private Container $container,
        private DatabaseConfig $databaseConfig,
        private PDO $pdo,
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
        $tableBuilder = $migration->up(new TableBuilder());

        $this->pdo->query($tableBuilder->getQuery())->execute();

        Migration::create(
            name: $migration->getName(),
        );

        event(new MigrationMigrated($migration->getName()));
    }
}
