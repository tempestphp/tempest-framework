<?php

declare(strict_types=1);

namespace Tempest\Testing;

use DateTimeInterface;
use PHPUnit\Framework\TestCase;
use Tempest\Application\AppConfig;
use Tempest\Application\Kernel;
use Tempest\Clock\Clock;
use Tempest\Clock\MockClock;
use Tempest\Console\Testing\ConsoleTester;
use Tempest\Container\Container;
use Tempest\Database\Migrations\MigrationManager;
use Tempest\Http\GenericRequest;
use Tempest\Http\Method;
use Tempest\Http\Request;
use Tempest\Testing\Http\HttpRouterTester;

abstract class IntegrationTest extends TestCase
{
    protected AppConfig $appConfig;

    protected Kernel $kernel;

    protected Container $container;

    protected ConsoleTester $console;

    protected HttpRouterTester $http;

    protected function setUp(): void
    {
        parent::setUp();

        $this->appConfig ??= new AppConfig(
            root: __DIR__ . '/../../',
            discoveryCache: true,
        );

        $this->kernel ??= new Kernel($this->appConfig);

        $this->container = $this->kernel->init();

        $this->console = $this->container->get(ConsoleTester::class);
        $this->http = $this->container->get(HttpRouterTester::class);

        $request = new GenericRequest(Method::GET, '/', []);
        $this->container->singleton(Request::class, fn () => $request);
        $this->container->singleton(GenericRequest::class, fn () => $request);
    }

    protected function migrate(string ...$migrationClasses): void
    {
        $migrationManager = $this->container->get(MigrationManager::class);

        foreach ($migrationClasses as $migrationClass) {
            $migrationManager->executeUp($this->container->get($migrationClass));
        }
    }

    protected function clock(DateTimeInterface|string $now): Clock
    {
        $this->container->singleton(Clock::class, fn () => new MockClock($now));

        return $this->container->get(Clock::class);
    }
}
