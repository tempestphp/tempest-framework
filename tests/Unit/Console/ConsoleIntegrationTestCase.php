<?php

declare(strict_types=1);

namespace Tests\Tempest\Unit\Console;

use PHPUnit\Framework\TestCase;
use Tempest\Application\AppConfig;
use Tempest\Application\Application;
use Tempest\Application\Kernel;
use Tempest\Console\ConsoleApplication;
use Tempest\Console\Input\ConsoleArgumentBag;
use Tempest\Console\Scheduler\NullShellExecutor;
use Tempest\Console\ShellExecutor;
use Tempest\Console\Testing\ConsoleTester;
use Tempest\Container\Container;
use Tempest\Discovery\DiscoveryLocation;

/**
 * @internal
 * @small
 */
class ConsoleIntegrationTestCase extends TestCase
{
    protected Container $container;
    protected ConsoleTester $console;

    protected function setUp(): void
    {
        parent::setUp();

        $appConfig = new AppConfig(
            root: getcwd(),
            enableExceptionHandling: true,
            discoveryCache: false,
            discoveryLocations: [
                new DiscoveryLocation('Tests\\Tempest\\Unit\\Console\\', __DIR__),
            ],
        );

        $kernel = new Kernel($appConfig);

        $this->container = $kernel->init();

        $application = new ConsoleApplication(
            container: $this->container,
            argumentBag: new ConsoleArgumentBag($_SERVER['argv']),
        );

        $this->container->singleton(Application::class, fn () => $application);
        $this->container->singleton(ShellExecutor::class, fn () => new NullShellExecutor());
        $this->console = new ConsoleTester($this->container);
    }
}
