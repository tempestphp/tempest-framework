<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Console\Commands;

use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

class MigrateCommandTest extends FrameworkIntegrationTestCase
{
    /** @test */
    public function test_migrate_command()
    {
        $this->console
            ->call('migrate')
            ->assertContains('create_migrations_table');
    }
}
