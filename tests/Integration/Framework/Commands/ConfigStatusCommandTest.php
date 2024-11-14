<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Framework\Commands;

use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 */
final class ConfigStatusCommandTest extends FrameworkIntegrationTestCase
{
    public function test_it_shows_config_in_json_format(): void
    {
        $this->console
            ->call('config:status --format=json --filter=database.config.php')
            ->assertJson()
            ->assertContains('database.config.php')
            ->assertContains('DatabaseConfig')
            ->assertDoesNotContain('views.config.php')
            ->assertContains('@type');
    }

    public function test_it_shows_config_in_file_format(): void
    {
        $this->console
            ->call('config:status --format=file --filter=database.config.php')
            ->assertContains('database.config.php')
            ->assertContains('DatabaseConfig')
            ->assertDoesNotContain('views.config.php')
            ->assertContains('<?php');
    }
}
