<?php

declare(strict_types=1);

namespace Tempest\Testing;

use PHPUnit\Framework\TestCase;
use Tempest\AppConfig;
use Tempest\Application\Kernel;
use Tempest\Container\Container;
use Tempest\Database\Migrations\MigrationManager;
use Tempest\Testing\Console\ConsoleCommandTester;
use Tempest\Testing\Http\HttpRouterTester;

abstract class IntegrationTest extends TestCase
{
    protected AppConfig $appConfig;

    protected Kernel $kernel;

    protected Container $container;

    protected ConsoleCommandTester $console;

    protected HttpRouterTester $http;

    protected function setUp(): void
    {
        parent::setUp();

        $this->appConfig = new AppConfig(
            discoveryCache: true,
        );

        $this->kernel = new Kernel(
            __DIR__ . '/../../',
            $this->appConfig
        );

        $this->container = $this->kernel->init();

        $this->console = $this->container->get(ConsoleCommandTester::class);
        $this->http = $this->container->get(HttpRouterTester::class);
    }

    protected function migrate(string ...$migrationClasses): void
    {
        $migrationManager = $this->container->get(MigrationManager::class);

        foreach ($migrationClasses as $migrationClass) {
            $migrationManager->executeUp($this->container->get($migrationClass));
        }
    }
}
