<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Console\Commands;

use Tempest\Database\Migrations\MigrationException;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 * @small
 */
class MigrateDownCommandTestCase extends FrameworkIntegrationTestCase
{
    public function test_migrate_rollback_command(): void
    {
        $this->console
            ->call('migrate:up')
            ->assertContains('create_migrations_table');

        $this->console
            ->call('migrate:down --force')
            ->assertContains('create_migrations_table')
            ->assertContains("Rolled back 3 migrations");
    }

    public function test_errors_when_no_migrations_to_rollback(): void
    {
        $this->console
            ->call('migrate:down')
            ->assertContains(MigrationException::noTable()->getMessage());
    }
}
