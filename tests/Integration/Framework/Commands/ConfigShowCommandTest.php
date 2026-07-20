<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Framework\Commands;

use PHPUnit\Framework\Attributes\Test;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 */
final class ConfigShowCommandTest extends FrameworkIntegrationTestCase
{
    #[Test]
    public function it_shows_config_without_params(): void
    {
        $this->console
            ->call('config:show')
            ->assertJson();
    }

    #[Test]
    public function it_shows_config_in_json_format(): void
    {
        $this->console
            ->call('config:show --format=pretty --filter=database.config.php')
            ->assertJson()
            ->assertContains('database.config.php')
            ->assertContains('SQLiteConfig')
            ->assertDoesNotContain('views.config.php')
            ->assertContains('@type');
    }

    #[Test]
    public function it_shows_config_in_file_format(): void
    {
        $this->console
            ->call('config:show --format=file --filter=database.config.php')
            ->assertContains('database.config.php')
            ->assertContains('SQLiteConfig')
            ->assertDoesNotContain('views.config.php')
            ->assertContains('<?php');
    }
}
