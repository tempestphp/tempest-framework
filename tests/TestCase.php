<?php

declare(strict_types=1);

namespace Tests\Tempest;

use _PHPStan_cc8d35ffb\Nette\Neon\Exception;
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
        /** @var Router $router */
        $router = $this->container->get(Router::class);

        // Let's check whether the current request matches a route
        $matchedRoute = $router->matchRoute($request);

        // If not, there's nothing left to do, we can't send this request
        if (! $matchedRoute) {
            throw new Exception("No matching route found for {$request->getMethod()->value} {$request->getPath()}");
        }

        // If we have a match, let's find out if our input request data matches what the route's action needs
        $requestClass = $request::class;

        // We'll loop over all the handler's parameters
        foreach ($matchedRoute->route->handler->getParameters() as $parameter) {
            // TODO: support unions

            // If the parameter's type is an instance of Request…
            if (is_a($parameter->getType()->getName(), Request::class, true)) {
                // We'll use that specific request class
                $requestClass = $parameter->getType()->getName();

                break;
            }
        }

        // We map the original request we got into this method to the right request class
        $request = map($request)->to($requestClass);

        // Finally, we register this newly created request object in the container
        // This makes it so that RequestInitializer is bypassed entirely when the controller action needs the request class
        // Making it so that we don't need to set any $_SERVER variables and stuff like that
        $this->container->singleton(Request::class, fn () => $request);
        $this->container->singleton($request::class, fn () => $request);

        // Ok, now finally for real, we dispatch our request and return the response
        return $router->dispatch($request);
    }
}
