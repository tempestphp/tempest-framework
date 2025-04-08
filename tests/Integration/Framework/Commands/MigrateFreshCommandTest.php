<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Framework\Commands;

use PHPUnit\Framework\Assert;
use Tempest\Console\ExitCode;
use Tempest\Database\Migrations\Migration;
use Tempest\Framework\Commands\MigrateFreshCommand;
use Tempest\Framework\Commands\MigrateUpCommand;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 */
final class MigrateFreshCommandTest extends FrameworkIntegrationTestCase
{
    public function test_migrate_fresh_command(): void
    {
        $this->console
            ->call(MigrateUpCommand::class)
            ->assertContains('create_migrations_table')
            ->assertContains('MIGRATED')
            ->assertSuccess();

        $this->console
            ->call(MigrateFreshCommand::class)
            ->assertContains('DROPPING')
            ->assertNotSee('There is no migration to drop')
            ->assertSuccess();

        Assert::assertNotEmpty(Migration::all());
    }

    public function test_migrate_fresh_command_inserts_new_records(): void
    {
        $this->console
            ->call(MigrateFreshCommand::class)
            ->assertContains('create_migrations_table');

        Assert::assertNotEmpty(Migration::all());
    }

    public function test_migrate_fresh_command_fails_with_validate_when_migrations_are_tampered_with(): void
    {
        $this->console
            ->call(MigrateFreshCommand::class)
            ->assertContains('Migration files are valid')
            ->assertExitCode(ExitCode::SUCCESS);

        $migrations = Migration::all();
        foreach ($migrations as $migration) {
            $migration->hash = 'invalid-hash';
            $migration->save();
        }

        $this->console
            ->call(MigrateFreshCommand::class)
            ->assertExitCode(ExitCode::INVALID);
    }

    public function test_migrate_fresh_command_skips_validation_and_runs_if_specified(): void
    {
        $this->console
            ->call(MigrateFreshCommand::class)
            ->assertContains('Migration files are valid')
            ->assertExitCode(ExitCode::SUCCESS);

        $migrations = Migration::all();
        foreach ($migrations as $migration) {
            $migration->hash = 'invalid-hash';
            $migration->save();
        }

        $this->console
            ->call('migrate:fresh --no-validate')
            ->assertExitCode(ExitCode::SUCCESS);
    }
}
