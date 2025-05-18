<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Core;

use Tempest\Console\Commands\AboutCommand;
use Tempest\Core\AppConfig;
use Tempest\Core\Kernel;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 */
final class AboutCommandTest extends FrameworkIntegrationTestCase
{
    public function test_about(): void
    {
        $this->console
            ->call(AboutCommand::class)
            ->assertSee('ENVIRONMENT')
            ->assertSee('Tempest version')
            ->assertSee('PHP version')
            ->assertSee('Composer version')
            ->assertSee('Operating system')
            ->assertSee('Environment')
            ->assertSee('Application URL')
            ->assertSee('DATABASE')
            ->assertSee('Engine')
            ->assertSee('Version');
    }

    public function test_shows_current_uri(): void
    {
        $this->container->get(AppConfig::class)->baseUri = 'https://tempestphp.test';

        $this->console
            ->call(AboutCommand::class)
            ->assertSee('https://tempestphp.test');
    }

    public function test_shows_current_kernel_version(): void
    {
        $this->console
            ->call(AboutCommand::class)
            ->assertSee(Kernel::VERSION);
    }

    public function test_json(): void
    {
        $this->console
            ->call(AboutCommand::class, ['json' => true])
            ->assertJson();
    }
}
