<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Tempest\Console\ConsoleApplication;
use Tempest\Console\Input\ConsoleArgumentBag;
use Tempest\Console\Output\MemoryOutputBuffer;
use Tempest\Console\Output\StdoutOutputBuffer;
use Tempest\Console\OutputBuffer;
use Tempest\Console\Scheduler\NullShellExecutor;
use Tempest\Console\ShellExecutor;
use Tempest\Console\Testing\ConsoleTester;
use Tempest\Core\AppConfig;
use Tempest\Core\Application;
use Tempest\Core\DiscoveryLocation;
use Tempest\Database\DatabaseConfig;
use Tempest\Database\Migrations\MigrationManager;
use Tempest\Framework\Testing\IntegrationTest;
use Tempest\Http\HttpApplication;
use Tempest\Support\PathHelper;
use Tempest\View\GenericView;
use Tempest\View\View;
use Tempest\View\ViewRenderer;

abstract class FrameworkIntegrationTestCase extends IntegrationTest
{
    protected function setUp(): void
    {
        $this->root = __DIR__ . '/../../';
        $this->discoveryLocations = [
            new DiscoveryLocation('Tests\\Tempest\\Integration\\Console\\Fixtures', __DIR__ . '/Console/Fixtures'),
            new DiscoveryLocation('Tests\\Tempest\\Fixtures', __DIR__ . '/../Fixtures'),
        ];

        parent::setUp();

        // Console
        $this->container->singleton(OutputBuffer::class, fn () => new MemoryOutputBuffer());
        $this->container->singleton(StdoutOutputBuffer::class, fn () => new MemoryOutputBuffer());
        $this->container->singleton(ShellExecutor::class, fn () => new NullShellExecutor());

        $this->console = new ConsoleTester($this->container);

        // Database
        $databaseConfigPath = __DIR__ . '/../Fixtures/Config/database.config.php';

        if (! file_exists($databaseConfigPath)) {
            copy(__DIR__ . '/../Fixtures/Config/database.sqlite.php', $databaseConfigPath);
        }

        $connection = (require $databaseConfigPath)->connection();
        $config = $this->container->get(DatabaseConfig::class);
        $config->connection = $connection;

        $this->rollbackDatabase();
    }

    protected function actAsConsoleApplication(string $command = ''): Application
    {
        $application = new ConsoleApplication(
            container: $this->container,
            appConfig: $this->container->get(AppConfig::class),
            argumentBag: new ConsoleArgumentBag(['tempest', ...explode(' ', $command)]),
        );

        $this->container->singleton(Application::class, fn () => $application);

        return $application;
    }

    protected function actAsHttpApplication(): HttpApplication
    {
        $application = new HttpApplication(
            $this->container,
        );

        $this->container->singleton(Application::class, fn () => $application);

        return $application;
    }

    protected function render(string|View $view, mixed ...$params): string
    {
        if (is_string($view)) {
            $view = new GenericView($view);
        }

        $view->data(...$params);

        return $this->container->get(ViewRenderer::class)->render($view);
    }

    protected function rollbackDatabase(): void
    {
        $migrationManager = $this->container->get(MigrationManager::class);

        $migrationManager->dropAll();
    }
}
