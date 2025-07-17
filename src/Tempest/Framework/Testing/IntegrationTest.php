<?php

declare(strict_types=1);

namespace Tempest\Framework\Testing;

use PHPUnit\Framework\TestCase;
use Tempest\Cache\Testing\CacheTester;
use Tempest\Clock\Clock;
use Tempest\Clock\MockClock;
use Tempest\Console\Testing\ConsoleTester;
use Tempest\Container\GenericContainer;
use Tempest\Core\AppConfig;
use Tempest\Core\ExceptionTester;
use Tempest\Core\FrameworkKernel;
use Tempest\Core\Kernel;
use Tempest\Database\Migrations\MigrationManager;
use Tempest\DateTime\DateTimeInterface;
use Tempest\EventBus\EventBus;
use Tempest\EventBus\Testing\EventBusTester;
use Tempest\Framework\Testing\Http\HttpRouterTester;
use Tempest\Http\GenericRequest;
use Tempest\Http\Method;
use Tempest\Http\Request;
use Tempest\Mail\MailerConfig;
use Tempest\Mail\Testing\MailTester;
use Tempest\Mail\Testing\TestingMailer;
use Tempest\Storage\Testing\StorageTester;
use Tempest\View\ViewRenderer;

use function Tempest\Support\Path\normalize;

/** @mago-expect maintainability/too-many-properties */
abstract class IntegrationTest extends TestCase
{
    protected string $root;

    /** @var \Tempest\Discovery\DiscoveryLocation[] */
    protected array $discoveryLocations = [];

    protected AppConfig $appConfig;

    protected Kernel $kernel;

    protected GenericContainer $container;

    protected ConsoleTester $console;

    protected HttpRouterTester $http;

    protected InstallerTester $installer;

    protected ViteTester $vite;

    protected EventBusTester $eventBus;

    protected StorageTester $storage;

    protected MailTester $mailer;

    protected CacheTester $cache;

    protected ExceptionTester $exceptions;

    protected function setUp(): void
    {
        parent::setUp();

        // We force forward slashes for consistency even on Windows.
        $this->root ??= normalize(realpath(__DIR__ . '/../../'));

        $this->kernel ??= FrameworkKernel::boot(
            root: $this->root,
            discoveryLocations: $this->discoveryLocations,
        );

        /** @var GenericContainer $container */
        $container = $this->kernel->container;
        $this->container = $container;

        $this->console = new ConsoleTester($this->container);
        $this->http = new HttpRouterTester($this->container);
        $this->installer = new InstallerTester($this->container);
        $this->eventBus = new EventBusTester($this->container);
        $this->storage = new StorageTester($this->container);
        $this->cache = new CacheTester($this->container);
        $this->mailer = new MailTester(new TestingMailer(
            eventBus: $this->container->get(EventBus::class),
        ));

        $this->exceptions = $this->container->get(ExceptionTester::class);
        $this->exceptions->preventReporting();

        $this->vite = $this->container->get(ViteTester::class);
        $this->vite->preventTagResolution();
        $this->vite->clearCaches();

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

    protected function clock(DateTimeInterface|string $now = 'now'): MockClock
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
