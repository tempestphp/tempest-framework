<?php

declare(strict_types=1);

namespace Tests\Tempest;

use Tempest\AppConfig;
use Tempest\Application\Application;
use Tempest\Application\ConsoleApplication;
use Tempest\Application\HttpApplication;
use Tempest\Application\Kernel;
use Tempest\Console\ConsoleOutput;
use Tempest\Container\Container;
use Tempest\Database\Migrations\MigrationManager;
use function Tempest\get;
use Tempest\Http\Request;
use Tempest\Http\Response;
use Tempest\Http\Router;
use function Tempest\map;
use Tempest\Support\ArrayHelper;

abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    protected Container $container;

    protected Kernel $kernel;

    protected AppConfig $appConfig;

    protected function setUp(): void
    {
        parent::setUp();

        $databasePath = __DIR__ . '/../app/database.sqlite';
        $cleanDatabasePath = __DIR__ . '/../app/database-clean.sqlite';

        @unlink($databasePath);
        copy($cleanDatabasePath, $databasePath);

        $this->appConfig = new AppConfig(
            discoveryCache: true,
            enableExceptionHandling: false,
        );

        $this->kernel = new Kernel(__DIR__ . '/../', $this->appConfig);

        $this->container = $this->kernel->init();

        $this->container->singleton(
            ConsoleOutput::class,
            fn () => new TestConsoleOutput(),
        );
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
        $application = $this->actAsConsoleApplication($command);

        $application->run();

        return $this->container->get(ConsoleOutput::class);
    }

    protected function actAsConsoleApplication(string $command = ''): Application
    {
        $application = new ConsoleApplication(
            args: ['tempest', ...explode(' ', $command)],
            container: $this->container,
            appConfig: $this->container->get(AppConfig::class),
        );

        $this->container->singleton(Application::class, fn () => $application);

        return $application;
    }

    protected function actAsHttpApplication(): HttpApplication
    {
        $application = new HttpApplication(
            $this->container,
            $this->container->get(AppConfig::class),
        );

        $this->container->singleton(Application::class, fn () => $application);

        return $application;
    }

    protected function send(Request $request): Response
    {
        // Convert body dot notation `field.nested` into real arrays
        // TODO: we might consider moving this logic to the ORM instead
        $body = (new ArrayHelper())->unwrap($request->getBody());

        $request = map([
            'method' => $request->getMethod(),
            'uri' => $request->getUri(),
            'body' => $body,
            'headers' => $request->getHeaders(),
            'path' => $request->getPath(),
            'query' => $request->getQuery(),
            ...$body,
        ])->to($request::class);

        $this->container->singleton(Request::class, fn () => $request);
        $this->container->singleton($request::class, fn () => $request);

        $router = $this->container->get(Router::class);

        return $router->dispatch($request);
    }
}
