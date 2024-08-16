<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Framework\Commands;

use Tempest\Database\Migrations\MigrationException;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 * @small
 */
class MigrateDownCommandTest extends FrameworkIntegrationTestCase
{
    public function test_migrate_rollback_command(): void
    {
        $this->console
            ->call('migrate:up --force')
            ->assertContains('create_migrations_table');

        $this->console
            ->call('migrate:down --force')
            ->assertContains('create_migrations_table')
            ->assertContains("Rolled back");
    }

    public function test_errors_when_no_migrations_to_rollback(): void
    {
        $this->console
            ->call('migrate:down')
            ->assertContains(MigrationException::noTable()->getMessage());
    }
}
