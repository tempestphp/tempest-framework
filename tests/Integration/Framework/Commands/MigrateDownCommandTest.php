<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Framework\Commands;

use PHPUnit\Framework\Attributes\Test;
use Tempest\Framework\Commands\MigrateDownCommand;
use Tempest\Framework\Commands\MigrateUpCommand;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 */
final class MigrateDownCommandTest extends FrameworkIntegrationTestCase
{
    #[Test]
    public function migrate_rollback_command(): void
    {
        $this->console
            ->call(MigrateUpCommand::class, ['force' => true])
            ->assertContains('create_migrations_table');

        $this->console
            ->call(MigrateDownCommand::class, ['force' => true])
            ->assertContains('create_migrations_table')
            ->assertContains('ROLLED BACK');
    }

    #[Test]
    public function errors_when_no_migrations_to_rollback(): void
    {
        $this->console
            ->call(MigrateDownCommand::class)
            ->assertContains('There is no migration to roll back.');
    }
}
