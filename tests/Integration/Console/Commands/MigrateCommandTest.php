<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Console\Commands;

use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 * @small
 */
class MigrateCommandTest extends FrameworkIntegrationTestCase
{
    public function test_migrate_command()
    {
        $this->console
            ->call('migrate')
            ->assertContains('create_migrations_table');
    }
}
