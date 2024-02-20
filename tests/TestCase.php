<?php

declare(strict_types=1);

namespace Tests\Tempest;

use App\AppPackage;
use Tempest\AppConfig;
use Tempest\Application\ConsoleApplication;
use Tempest\Application\Kernel;
use Tempest\Database\Migrations\MigrationManager;
use function Tempest\get;
use Tempest\Http\Method;
use Tempest\Interface\ConsoleOutput;
use Tempest\Interface\Container;
use Tempest\TempestPackage;

abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    protected Container $container;

    protected Kernel $kernel;

    protected function setUp(): void
    {
        parent::setUp();

        $databasePath = __DIR__ . '/../app/database.sqlite';
        $cleanDatabasePath = __DIR__ . '/../app/database-clean.sqlite';

        @unlink($databasePath);
        copy($cleanDatabasePath, $databasePath);

        $this->kernel = new Kernel(__DIR__ . '/../', new AppConfig(
            discoveryLocations: [
                new TempestPackage(),
                new AppPackage(),
            ],
        ));

        $this->container = $this->kernel->init();

        $this->container
            ->addInitializer(new TestServerInitializer());

        $this->container->singleton(
            ConsoleOutput::class,
            fn () => new TestConsoleOutput(new TestConsoleFormatter()),
        );
    }

    protected function server(
        Method $method = Method::GET,
        string $uri = '/',
        array $body = [],
    ): void {
        $this->container->addInitializer(new TestServerInitializer(
            method: $method,
            uri: $uri,
            body: $body,
        ));
    }

    protected function migrate(string ...$migrationClasses): void
    {
        $migrationManager = get(MigrationManager::class);

        foreach ($migrationClasses as $migrationClass) {
            $migrationManager->executeUp(get($migrationClass));
        }
    }

    protected function console(string $command): TestConsoleOutput
    {
        $application = new ConsoleApplication(
            args: ['tempest', ...explode(' ', $command)],
            container: $this->container,
        );

        $application->run();

        return $this->container->get(ConsoleOutput::class);
    }
}
