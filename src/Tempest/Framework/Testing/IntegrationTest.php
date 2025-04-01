<?php

declare(strict_types=1);

namespace Tempest\Framework\Testing;

use DateTimeInterface;
use PHPUnit\Framework\TestCase;
use Tempest\Clock\Clock;
use Tempest\Clock\MockClock;
use Tempest\Console\Testing\ConsoleTester;
use Tempest\Container\Container;
use Tempest\Core\AppConfig;
use Tempest\Core\FrameworkKernel;
use Tempest\Core\Kernel;
use Tempest\Database\Migrations\MigrationManager;
use Tempest\Framework\Testing\Http\HttpRouterTester;
use Tempest\Http\Method;
use Tempest\Router\GenericRequest;
use Tempest\Router\Request;

use function Tempest\Support\Path\normalize;

abstract class IntegrationTest extends TestCase
{
    protected string $root;

    /** @var \Tempest\Discovery\DiscoveryLocation[] */
    protected array $discoveryLocations = [];

    protected AppConfig $appConfig;

    protected Kernel $kernel;

    protected Container $container;

    protected ConsoleTester $console;

    protected HttpRouterTester $http;

    protected InstallerTester $installer;

    protected ViteTester $vite;

    protected function setUp(): void
    {
        parent::setUp();

        // We force forward slashes for consistency even on Windows.
        $this->root ??= normalize(realpath(__DIR__ . '/../../'));

        $this->kernel ??= FrameworkKernel::boot(
            root: $this->root,
            discoveryLocations: $this->discoveryLocations,
        );

        $this->container = $this->kernel->container;

        $this->console = $this->container->get(ConsoleTester::class);
        $this->http = $this->container->get(HttpRouterTester::class);
        $this->installer = $this->container->get(InstallerTester::class);

        $this->vite = $this->container->get(ViteTester::class);
        $this->vite->preventTagResolution();

        $request = new GenericRequest(Method::GET, '/', []);
        $this->container->singleton(Request::class, fn () => $request);
        $this->container->singleton(GenericRequest::class, fn () => $request);
    }

    protected function migrate(string|object ...$migrationClasses): void
    {
        $migrationManager = $this->container->get(MigrationManager::class);

        foreach ($migrationClasses as $migrationClass) {
            $migration = is_string($migrationClass) ? $this->container->get($migrationClass) : $migrationClass;

            $migrationManager->executeUp($migration);
        }
    }

    protected function clock(DateTimeInterface|string $now): MockClock
    {
        $clock = new MockClock($now);

        $this->container->singleton(Clock::class, fn () => $clock);

        return $clock;
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        /** @phpstan-ignore-next-line */
        unset($this->root);
        /** @phpstan-ignore-next-line */
        unset($this->discoveryLocations);
        /** @phpstan-ignore-next-line */
        unset($this->appConfig);
        /** @phpstan-ignore-next-line */
        unset($this->kernel);
        /** @phpstan-ignore-next-line */
        unset($this->container);
        /** @phpstan-ignore-next-line */
        unset($this->console);
        /** @phpstan-ignore-next-line */
        unset($this->http);
    }
}
