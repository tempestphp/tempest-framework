<?php

declare(strict_types=1);

namespace Integration\Console\Commands;

use PDOException;
use PHPUnit\Framework\Assert;
use Tempest\Database\Migrations\Migration;
use Tempest\Database\Migrations\MigrationException;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 * @small
 */
class MigrateRollbackCommandTest extends FrameworkIntegrationTestCase
{
    public function test_migrate_rollback_command(): void
    {
        $this->console->call('migrate:up')
            ->assertContains('create_migrations_table');

        $this->console
            ->call('migrate:down')
            ->assertContains('create_migrations_table')
            ->assertContains("Rolled back 3 migrations");

        try {
            Assert::assertEmpty(Migration::all());
        } catch (PDOException $e) {
            Assert::assertStringContainsString('no such table: Migration', $e->getMessage());
        }
    }

    public function test_errors_when_no_migrations_to_rollback(): void
    {
        $this->console
            ->call('migrate:down')
            ->assertContains(MigrationException::noTable()->getMessage());
    }
}
