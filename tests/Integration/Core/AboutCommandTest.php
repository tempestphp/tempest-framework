<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Core;

use PHPUnit\Framework\Attributes\Test;
use Tempest\Cache\Config\InMemoryCacheConfig;
use Tempest\Console\Commands\AboutCommand;
use Tempest\Core\AppConfig;
use Tempest\Core\Kernel;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 */
final class AboutCommandTest extends FrameworkIntegrationTestCase
{
    #[Test]
    public function about(): void
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

    #[Test]
    public function shows_current_uri(): void
    {
        $this->container->get(AppConfig::class)->baseUri = 'https://tempestphp.test';

        $this->console
            ->call(AboutCommand::class)
            ->assertSee('https://tempestphp.test');
    }

    #[Test]
    public function shows_current_kernel_version(): void
    {
        $this->console
            ->call(AboutCommand::class)
            ->assertSee(Kernel::VERSION);
    }

    #[Test]
    public function json(): void
    {
        $this->console
            ->call(AboutCommand::class, ['json' => true])
            ->assertJson();
    }

    #[Test]
    public function cache(): void
    {
        $this->console
            ->call(AboutCommand::class)
            ->assertSee('INTERNAL CACHES')
            ->assertSee('USER CACHES')
            ->assertSee('Filesystem,');
    }

    #[Test]
    public function another_cache(): void
    {
        $this->container->config(new InMemoryCacheConfig());

        $this->console
            ->call(AboutCommand::class)
            ->assertSee('In-memory,');
    }
}
