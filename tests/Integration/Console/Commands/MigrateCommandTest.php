<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Console\Commands;

use PHPUnit\Framework\Assert;
use Tempest\Database\Migrations\Migration;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 * @small
 */
class MigrateCommandTest extends FrameworkIntegrationTestCase
{
    public function test_migrate_command(): void
    {
        $this->console
            ->call('migrate')
            ->assertContains('create_migrations_table')
            ->assertContains('Migrated 3 migrations');

        Assert::assertCount(3, Migration::all());
    }

    public function test_migrate_command_inserts_new_records(): void
    {
        $this->console
            ->call('migrate')
            ->assertContains('create_migrations_table');

        Assert::assertCount(3, Migration::all());
    }
}
