<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Console\Commands;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tempest\Console\Enums\ConfigType;
use Tempest\Core\ComposerNamespace;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;
use function Tempest\Support\str;

/**
 * @internal
 */
final class MakeConfigCommandTest extends FrameworkIntegrationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->installer->configure(
            __DIR__ . '/install',
            new ComposerNamespace('App\\', __DIR__ . '/install/App'),
        );
    }

    protected function tearDown(): void
    {
        $this->installer->clean();

        parent::tearDown();
    }

    #[Test]
    public function make_database_config(): void {
        $this->console
            ->call('make:config ' . ConfigType::DATABASE->value)
            ->submit();

        $this->installer
            ->assertFileExists('App/database.config.php')
            ->assertFileContains('App/database.config.php', 'use Tempest\Database\DatabaseConfig')
            ->assertFileContains('App/database.config.php', 'return new DatabaseConfig');
    }

    #[Test]
    public function make_twig_config(): void {
        $this->console
            ->call('make:config ' . ConfigType::TWIG->value)
            ->submit();

        $this->installer
            ->assertFileExists('App/twig.config.php')
            ->assertFileContains('App/twig.config.php', 'use Tempest\View\Renderers\TwigConfig')
            ->assertFileContains('App/twig.config.php', 'return new TwigConfig');
    }

    #[Test]
    public function make_blade_config(): void {
        $this->console
            ->call('make:config ' . ConfigType::BLADE->value)
            ->submit();

        $this->installer
            ->assertFileExists('App/blade.config.php')
            ->assertFileContains('App/blade.config.php', 'use Tempest\View\Renderers\BladeConfig')
            ->assertFileContains('App/blade.config.php', 'return new BladeConfig');
    }

    #[Test]
    public function make_view_config(): void {
        $this->console
            ->call('make:config ' . ConfigType::VIEW->value)
            ->submit();

        $this->installer
            ->assertFileExists('App/view.config.php')
            ->assertFileContains('App/view.config.php', 'use Tempest\View\ViewConfig')
            ->assertFileContains('App/view.config.php', 'return new ViewConfig');
    }

    #[Test]
    public function make_event_bus_config(): void {
        $this->console
            ->call('make:config ' . ConfigType::EVENT_BUS->value)
            ->submit();

        $this->installer
            ->assertFileExists('App/event-bus.config.php')
            ->assertFileContains('App/event-bus.config.php', 'use Tempest\EventBus\EventBusConfig')
            ->assertFileContains('App/event-bus.config.php', 'return new EventBusConfig');
    }

    #[Test]
    public function make_command_bus_config(): void {
        $this->console
            ->call('make:config ' . ConfigType::COMMAND_BUS->value)
            ->submit();

        $this->installer
            ->assertFileExists('App/command-bus.config.php')
            ->assertFileContains('App/command-bus.config.php', 'use Tempest\CommandBus\CommandBusConfig')
            ->assertFileContains('App/command-bus.config.php', 'return new CommandBusConfig');
    }

    #[Test]
    public function make_log_config(): void {
        $this->console
            ->call('make:config ' . ConfigType::LOG->value)
            ->submit();

        $this->installer
            ->assertFileExists('App/log.config.php')
            ->assertFileContains('App/log.config.php', 'use Tempest\Log\LogConfig')
            ->assertFileContains('App/log.config.php', 'return new LogConfig');
    }

    #[Test]
    public function make_cache_config(): void {
        $this->console
            ->call('make:config ' . ConfigType::CACHE->value)
            ->submit();

        $this->installer
            ->assertFileExists('App/cache.config.php')
            ->assertFileContains('App/cache.config.php', 'use Tempest\Cache\CacheConfig')
            ->assertFileContains('App/cache.config.php', 'return new CacheConfig');
    }

    #[Test]
    public function make_console_config(): void {
        $this->console
            ->call('make:config ' . ConfigType::CONSOLE->value)
            ->submit();

        $this->installer
            ->assertFileExists('App/console.config.php')
            ->assertFileContains('App/console.config.php', 'use Tempest\Console\ConsoleConfig')
            ->assertFileContains('App/console.config.php', 'return new ConsoleConfig');
    }
}
