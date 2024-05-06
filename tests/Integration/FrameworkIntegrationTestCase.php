<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration;

use Tempest\AppConfig;
use Tempest\Application;
use Tempest\Application\HttpApplication;
use Tempest\Console\ConsoleApplication;
use Tempest\Console\ConsoleArgumentBag;
use Tempest\Console\Output\MemoryOutputBuffer;
use Tempest\Console\Output\OutputBuffer;
use Tempest\Console\Output\StdoutOutputBuffer;
use Tempest\Discovery\DiscoveryDiscovery;
use Tempest\Discovery\DiscoveryLocation;
use Tempest\Testing\IntegrationTest;

abstract class FrameworkIntegrationTestCase extends IntegrationTest
{
    protected function setUp(): void
    {
        $this->appConfig = new AppConfig(
            root: __DIR__ . '/../../',
            discoveryCache: true,
            discoveryLocations: [
                new DiscoveryLocation(
                    'App\\',
                    __DIR__ . '/../../app',
                ),
            ],
        );

        parent::setUp();

        $this->container->singleton(OutputBuffer::class, fn () => new MemoryOutputBuffer());
        $this->container->singleton(StdoutOutputBuffer::class, fn () => new MemoryOutputBuffer());

        $databasePath = __DIR__ . '/../../app/database.sqlite';
        $cleanDatabasePath = __DIR__ . '/../../app/database-clean.sqlite';

        @unlink(DiscoveryDiscovery::CACHE_PATH);
        @unlink($databasePath);
        copy($cleanDatabasePath, $databasePath);
    }

    protected function actAsConsoleApplication(string $command = ''): Application
    {
        $application = new ConsoleApplication(
            argumentBag: new ConsoleArgumentBag(['tempest', ...explode(' ', $command)]),
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
}
