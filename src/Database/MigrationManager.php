<?php

namespace Tempest\Database;

use PDO;
use Tempest\Database\TableBuilder\TableBuilder;
use Tempest\Interfaces\Container;
use Tempest\Interfaces\DatabaseMigration;
use Tempest\ORM\CreateMigrationsTable;
use Tempest\ORM\Migration;

final readonly class MigrationManager
{
    public function __construct(
        private Container $container,
        private DatabaseConfig $databaseConfig,
        private PDO $pdo,
    ) {}

    public function up(): void
    {
        try {
            $existingMigrations = Migration::query()->get();
        } catch (\PDOException) {
            $this->executeUp(new CreateMigrationsTable());

            $existingMigrations = Migration::query()->get();
        }

        $existingMigrations = array_map(
            fn (Migration $migration) => $migration->name,
            $existingMigrations,
        );

        foreach ($this->databaseConfig->migrations as $migrationClassName) {
            /** @var DatabaseMigration $migration */
            $migration = $this->container->get($migrationClassName);

            if (in_array($migration->getName(), $existingMigrations)) {
                continue;
            }

            $this->executeUp($migration);
        }
    }

    private function executeUp(DatabaseMigration $migration): void
    {
        $tableBuilder = $migration->up(new TableBuilder());

        $this->pdo->query($tableBuilder->getQuery())->execute();

        Migration::create(
            name: $migration->getName(),
        );
//
//        $this->pdo
//            ->prepare(<<<SQL
//                INSERT INTO Migration (name) VALUES (:migration_name);
//                SQL,
//            )
//            ->execute([
//                'migration_name' => $migration->getName()
//            ]);
    }
}