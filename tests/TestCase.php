<?php

namespace Tests\Tempest\Console;

use Tempest\AppConfig;
use Tempest\Application;
use Tempest\Console\ConsoleApplication;
use Tempest\Console\ConsoleArgumentBag;
use Tempest\Console\Testing\Console\ConsoleCommandTester;
use Tempest\Container\Container;
use Tempest\Discovery\DiscoveryLocation;
use Tempest\Kernel;

class TestCase extends \PHPUnit\Framework\TestCase
{
    protected Container $container;
    protected ConsoleCommandTester $console;

    protected function setUp(): void
    {
        parent::setUp();

        $appConfig = new AppConfig(
            root: getcwd(),
            enableExceptionHandling: true,
            discoveryLocations: [
                new DiscoveryLocation('App\\', __DIR__ . '/../app/')
            ],
        );

        $kernel = new Kernel($appConfig);

        $this->container = $kernel->init();

        $application = new ConsoleApplication(
            argumentBag: new ConsoleArgumentBag($_SERVER['argv']),
            container: $this->container,
            appConfig: $appConfig,
        );

        $this->container->singleton(Application::class, fn () => $application);
        $this->console = new ConsoleCommandTester($this->container);
    }
}