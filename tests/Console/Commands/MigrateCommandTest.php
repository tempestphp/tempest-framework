<?php

namespace Tests\Tempest\Console\Commands;

use Tests\Tempest\TestCase;

class MigrateCommandTest extends TestCase
{
    /** @test */
    public function test_migrate_command()
    {
        $output = $this->console('migrate')->asText();

        $this->assertStringContainsString('create_migrations_table', $output);
    }
}
