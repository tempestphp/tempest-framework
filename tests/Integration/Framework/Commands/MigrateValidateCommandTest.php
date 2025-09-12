<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Framework\Commands;

use PHPUnit\Framework\Attributes\PreCondition;
use Tempest\Console\ExitCode;
use Tempest\Database\MigratesDown;
use Tempest\Database\Migrations\Migration;
use Tempest\Database\Migrations\RunnableMigrations;
use Tempest\Database\QueryStatement;
use Tempest\Database\QueryStatements\RawStatement;
use Tempest\Framework\Commands\MigrateFreshCommand;
use Tempest\Framework\Commands\MigrateValidateCommand;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 */
final class MigrateValidateCommandTest extends FrameworkIntegrationTestCase
{
    #[PreCondition]
    protected function configure(): void
    {
        $this->container->singleton(
            className: RunnableMigrations::class,
            definition: new RunnableMigrations(
                migrations: [
                    ...iterator_to_array($this->container->get(RunnableMigrations::class)->getIterator()),
                    new DownMigrationForRehash(),
                ],
            ),
        );
    }

    public function test_migration_validate_command_verifies_valid_migrations(): void
    {
        $this->console
            ->call(MigrateValidateCommand::class)
            ->assertContains('Migration files are valid');
    }

    public function test_migration_validate_command_fails_when_migrations_are_tampered_with(): void
    {
        $this->console->call(MigrateFreshCommand::class, ['force' => true]);

        foreach (Migration::all() as $migration) {
            $migration->hash = 'invalid-hash';
            $migration->save();
        }

        $this->console
            ->call(MigrateValidateCommand::class)
            ->assertContains('HASH MISMATCH')
            ->assertExitCode(ExitCode::ERROR);
    }

    public function test_migration_validate_command_fails_when_migration_file_is_missing(): void
    {
        $this->console->call(MigrateFreshCommand::class, ['force' => true]);

        // Creating a migration record to simulate a missing migration file.
        Migration::create(
            name: 'create_missing_migration_file',
            hash: 'hash',
        );

        $this->console
            ->call(MigrateValidateCommand::class)
            ->assertContains('MISSING FILE')
            ->assertExitCode(ExitCode::ERROR);
    }
}

final class DownMigrationForRehash implements MigratesDown
{
    private(set) string $name = '2025-10-12_down_migration_for_rehash_test';

    public function down(): QueryStatement
    {
        return new RawStatement('SELECT 1');
    }
}
