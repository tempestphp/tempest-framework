<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Framework\Commands;

use Tempest\Console\ExitCode;
use Tempest\Database\Migrations\Migration;
use Tempest\Drift\FrameworkIntegrationTestCase;
use Tempest\Framework\Commands\MigrateFreshCommand;
use Tempest\Framework\Commands\MigrateRehashCommand;

/**
 * @internal
 */
final class MigrateRehashCommandTest extends FrameworkIntegrationTestCase
{
    public function test_migrate_rehash_rehashes_all_existing_migrations(): void
    {
        $this->console->call(MigrateFreshCommand::class, ['force' => true]);

        foreach (Migration::all() as $migration) {
            $migration->hash = 'invalid-hash';
            $migration->save();
        }

        $this->console
            ->call(MigrateRehashCommand::class)
            ->assertContains('Migrations have been re-hashed')
            ->assertExitCode(ExitCode::SUCCESS);

        foreach (Migration::all() as $migration) {
            $this->assertNotSame('invalid-hash', $migration->hash);
        }
    }
}
