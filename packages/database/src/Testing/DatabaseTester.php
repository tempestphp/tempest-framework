<?php

namespace Tempest\Database\Testing;

use PHPUnit\Framework\Assert;
use Tempest\Container\Container;
use Tempest\Database\Migrations\MigrationManager;

use function Tempest\Database\query;

final class DatabaseTester
{
    public function __construct(
        private Container $container,
    ) {}

    /**
     * Resets the database by dropping all tables and re-running all migrations.
     *
     * @alias `reset()`
     */
    public function setup(bool $migrate = true): self
    {
        return $this->reset($migrate);
    }

    /**
     * Resets the database by dropping all tables and re-running all migrations.
     */
    public function reset(bool $migrate = true): self
    {
        $migrationManager = $this->container->get(MigrationManager::class);
        $migrationManager->dropAll();

        if ($migrate) {
            $this->migrate();
        }

        return $this;
    }

    /**
     * Migrates the specified migration classes. If no migration is specified, all application migrations will be run.
     */
    public function migrate(string|object ...$migrationClasses): void
    {
        $migrationManager = $this->container->get(MigrationManager::class);

        if (count($migrationClasses) === 0) {
            $migrationManager->up();
            return;
        }

        foreach ($migrationClasses as $migrationClass) {
            $migration = is_string($migrationClass) ? $this->container->get($migrationClass) : $migrationClass;

            $migrationManager->executeUp($migration);
        }
    }

    /**
     * Asserts that a row exists in the given table matching the provided data.
     */
    public function assertTableHasRow(string $table, mixed ...$data): void
    {
        $select = query($table)->count();

        foreach ($data as $key => $value) {
            $select->whereField($key, $value);
        }

        Assert::assertTrue($select->execute() > 0, "Failed asserting that a row in the table [{$table}] matches the given data.");
    }

    /**
     * Asserts that the given table contains the specified number of rows.
     */
    public function assertTableHasCount(string $table, int $count): void
    {
        Assert::assertSame(
            expected: $count,
            actual: query($table)->count()->execute(),
            message: "Failed asserting that the table [{$table}] contains [{$count}] rows.",
        );
    }

    /**
     * Asserts that there is no row in the given table matching the provided data.
     */
    public function assertTableDoesNotHaveRow(string $table, mixed ...$data): void
    {
        $select = query($table)->count();

        foreach ($data as $key => $value) {
            $select->whereField($key, $value);
        }

        Assert::assertTrue($select->execute() === 0, "Failed asserting that no row in the table [{$table}] matches the given data.");
    }

    /**
     * Asserts that the given table is empty.
     */
    public function assertTableEmpty(string $table): void
    {
        $this->assertTableHasCount($table, count: 0);
    }

    /**
     * Asserts that the given table is not empty.
     */
    public function assertTableNotEmpty(string $table): void
    {
        $this->assertTableHasRow($table);
    }
}
