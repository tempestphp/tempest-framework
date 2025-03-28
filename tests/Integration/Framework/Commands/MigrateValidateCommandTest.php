<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Framework\Commands;

use PHPUnit\Framework\Assert;
use Tempest\Console\ExitCode;
use Tempest\Database\Migrations\Migration;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 */
final class MigrateValidateCommandTest extends FrameworkIntegrationTestCase
{
    public function test_migration_validate_command_verifies_valid_migrations(): void
    {
        $this->console
            ->call('migrate:validate')
            ->assertContains('Migration files are valid');
    }

    public function test_migration_validate_command_fails_when_migrations_are_tampered_with(): void
    {
        $this->console
            ->call('migrate:fresh --force');

        $migrations = Migration::all();
        foreach ($migrations as $migration) {
            $migration->hash = 'invalid-hash';
            $migration->save();
        }

        $this->console
            ->call('migrate:validate')
            ->assertExitCode(ExitCode::ERROR);
    }
}
