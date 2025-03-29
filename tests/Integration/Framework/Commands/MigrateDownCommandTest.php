<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Framework\Commands;

use Tempest\Database\Migrations\TableNotFoundException;
use Tempest\Framework\Commands\MigrateDownCommand;
use Tempest\Framework\Commands\MigrateUpCommand;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 */
final class MigrateDownCommandTest extends FrameworkIntegrationTestCase
{
    public function test_migrate_rollback_command(): void
    {
        $this->console
            ->call(MigrateUpCommand::class, ['force' => true])
            ->assertContains('create_migrations_table');

        $this->console
            ->call(MigrateDownCommand::class, ['force' => true])
            ->assertContains('create_migrations_table')
            ->assertContains('Rolled back');
    }

    public function test_errors_when_no_migrations_to_rollback(): void
    {
        $this->console
            ->call(MigrateDownCommand::class)
            ->assertContains(new TableNotFoundException()->getMessage());
    }
}
