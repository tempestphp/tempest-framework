<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration;

use Tempest\AppConfig;
use Tempest\Application\Application;
use Tempest\Application\ConsoleApplication;
use Tempest\Application\HttpApplication;
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

        $databasePath = __DIR__ . '/../../app/database.sqlite';
        $cleanDatabasePath = __DIR__ . '/../../app/database-clean.sqlite';

        @unlink(DiscoveryDiscovery::CACHE_PATH);
        @unlink($databasePath);
        copy($cleanDatabasePath, $databasePath);
    }

    protected function actAsConsoleApplication(string $command = ''): Application
    {
        $application = new ConsoleApplication(
            args: ['tempest', ...explode(' ', $command)],
            container: $this->container,
            coreConfig: $this->container->get(AppConfig::class),
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
