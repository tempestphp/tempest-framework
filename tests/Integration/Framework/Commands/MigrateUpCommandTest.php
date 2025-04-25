<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Framework\Commands;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Attributes\CoversNothing;
use Tempest\Console\ExitCode;
use Tempest\Database\Migrations\Migration;
use Tempest\Framework\Commands\MigrateUpCommand;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 */
#[CoversNothing]
final class MigrateUpCommandTest extends FrameworkIntegrationTestCase
{
    public function test_migrate_command(): void
    {
        $this->console
            ->call(MigrateUpCommand::class)
            ->assertContains('create_migrations_table')
            ->assertContains('MIGRATED');

        Assert::assertNotEmpty(Migration::all());
    }

    public function test_migrate_command_inserts_new_records(): void
    {
        $this->console
            ->call(MigrateUpCommand::class)
            ->assertContains('create_migrations_table');

        Assert::assertNotEmpty(Migration::all());
    }

    public function test_migrate_command_validates_migrations(): void
    {
        $this->console
            ->call(MigrateUpCommand::class)
            ->assertContains('Migration files are valid')
            ->assertExitCode(ExitCode::SUCCESS);

        Assert::assertNotEmpty(Migration::all());
    }

    public function test_migrate_up_command_fails_when_migrations_are_tampered_with(): void
    {
        $this->console
            ->call(MigrateUpCommand::class)
            ->assertContains('Migration files are valid')
            ->assertExitCode(ExitCode::SUCCESS);

        foreach (Migration::all() as $migration) {
            $migration->hash = 'invalid-hash';
            $migration->save();
        }

        $this->console
            ->call(MigrateUpCommand::class)
            ->assertExitCode(ExitCode::INVALID);
    }

    public function test_migrate_up_command_skips_validation_and_runs_if_specified(): void
    {
        $this->console
            ->call(MigrateUpCommand::class)
            ->assertContains('Migration files are valid')
            ->assertExitCode(ExitCode::SUCCESS);

        foreach (Migration::all() as $migration) {
            $migration->hash = 'invalid-hash';
            $migration->save();
        }

        $this->console
            ->call('migrate:up --no-validate')
            ->assertExitCode(ExitCode::SUCCESS);
    }
}
