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
final class MigrateRehashCommandTest extends FrameworkIntegrationTestCase
{
    public function test_migrate_rehash_rehashes_all_existing_migrations(): void
    {
        $this->console
            ->call('migrate:fresh --force');

        $migrations = Migration::all();
        foreach ($migrations as $migration) {
            $migration->hash = 'invalid-hash';
            $migration->save();
        }

        $this->console
            ->call('migrate:rehash')
            ->assertContains('Rehashed all migrations')
            ->assertExitCode(ExitCode::SUCCESS);

        $migrations = Migration::all();
        foreach ($migrations as $migration) {
            Assert::assertNotSame('invalid-hash', $migration->hash);
        }
    }
}
