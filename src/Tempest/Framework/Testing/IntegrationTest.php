<?php

declare(strict_types=1);

namespace Tempest\Framework\Testing;

use Closure;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Tempest\Auth\OAuth\Testing\OAuthTester;
use Tempest\Cache\Testing\CacheTester;
use Tempest\Clock\Clock;
use Tempest\Clock\MockClock;
use Tempest\Console\Output\MemoryOutputBuffer;
use Tempest\Console\Output\StdoutOutputBuffer;
use Tempest\Console\OutputBuffer;
use Tempest\Console\Testing\ConsoleTester;
use Tempest\Container\GenericContainer;
use Tempest\Core\AppConfig;
use Tempest\Core\ExceptionTester;
use Tempest\Core\FrameworkKernel;
use Tempest\Core\Kernel;
use Tempest\Database\Migrations\CreateMigrationsTable;
use Tempest\Database\Migrations\MigrationManager;
use Tempest\Database\Testing\DatabaseTester;
use Tempest\DateTime\DateTimeInterface;
use Tempest\Discovery\DiscoveryLocation;
use Tempest\EventBus\Testing\EventBusTester;
use Tempest\Framework\Testing\Http\HttpRouterTester;
use Tempest\Http\GenericRequest;
use Tempest\Http\Method;
use Tempest\Http\Request;
use Tempest\Mail\Testing\MailTester;
use Tempest\Mail\Testing\TestingMailer;
use Tempest\Process\Testing\ProcessTester;
use Tempest\Storage\Testing\StorageTester;
use Throwable;

use function Tempest\Support\Path\normalize;
use function Tempest\Support\Path\to_absolute_path;

/**
 * @mago-expect lint:too-many-properties
 */
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

    protected ProcessTester $process;

    protected OAuthTester $oauth;

    protected DatabaseTester $database;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setupKernel()
            ->setupConsole()
            ->setupTesters()
            ->setupBaseRequest();
    }

    /**
     * Returns an array of DiscoveryLocations that should be discovered only during testing
     * @return \Tempest\Discovery\DiscoveryLocation[]
     */
    protected function discoverTestLocations(): array
    {
        $discoveryLocations = [];

        $testsPath = to_absolute_path($this->root, 'tests');

        if (is_dir($testsPath)) {
            $discoveryLocations[] = new DiscoveryLocation('Tests', $testsPath);
        }

        return $discoveryLocations;
    }

    protected function setupKernel(): self
    {
        // We force forward slashes for consistency even on Windows.
        $this->root ??= normalize(realpath(getcwd()));

        $discoveryLocations = [...$this->discoveryLocations, ...$this->discoverTestLocations()];

        $this->kernel ??= FrameworkKernel::boot(
            root: $this->root,
            discoveryLocations: $discoveryLocations,
        );

        /** @var GenericContainer $container */
        $container = $this->kernel->container;
        $this->container = $container;

        return $this;
    }

    protected function setupConsole(): self
    {
        $this->console = new ConsoleTester($this->container);
        $this->container->singleton(OutputBuffer::class, fn () => new MemoryOutputBuffer());
        $this->container->singleton(StdoutOutputBuffer::class, fn () => new MemoryOutputBuffer());

        return $this;
    }

    protected function setupTesters(): self
    {
        $this->http = new HttpRouterTester($this->container);
        $this->installer = new InstallerTester($this->container);
        $this->eventBus = new EventBusTester($this->container);
        $this->storage = new StorageTester($this->container);
        $this->cache = new CacheTester($this->container);
        $this->mailer = new MailTester(new TestingMailer());

        $this->process = $this->container->get(ProcessTester::class);
        $this->process->disableProcessExecution();

        $this->exceptions = $this->container->get(ExceptionTester::class);
        $this->exceptions->preventReporting();

        $this->vite = $this->container->get(ViteTester::class);
        $this->vite->preventTagResolution();
        $this->vite->clearCaches();

        $this->oauth = new OAuthTester($this->container);
        $this->database = new DatabaseTester($this->container);

        return $this;
    }

    protected function setupBaseRequest(): self
    {
        $request = new GenericRequest(Method::GET, '/', []);
        $this->container->singleton(Request::class, fn () => $request);
        $this->container->singleton(GenericRequest::class, fn () => $request);

        return $this;
    }

    /**
     * Cleans up the database and migrates the migrations using `migrateDatabase`.
     *
     * @deprecated Use `$this->database->setup()` instead.
     */
    protected function setupDatabase(): self
    {
        $migrationManager = $this->container->get(MigrationManager::class);
        $migrationManager->dropAll();

        $this->migrateDatabase();

        return $this;
    }

    /**
     * Creates the migration table. You may override this method to provide more migrations to run for every tests in this file.
     *
     * @deprecated Use `$this->database->migrate()` instead.
     */
    protected function migrateDatabase(): void
    {
        $this->migrate(CreateMigrationsTable::class);
    }

    /**
     * Migrates the specified migration classes.
     *
     * @deprecated Use `$this->database->migrate()` instead.
     */
    protected function migrate(string|object ...$migrationClasses): void
    {
        $this->database->migrate(...$migrationClasses);
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
        /** @phpstan-ignore-next-line */
        unset($this->oauth);
    }

    protected function assertException(string $expectedExceptionClass, Closure $handler, ?Closure $assertException = null, ?string $message = null): void
    {
        try {
            $handler();
        } catch (Throwable $throwable) {
            $this->assertInstanceOf($expectedExceptionClass, $throwable);

            if ($assertException !== null) {
                $assertException($throwable);
            }

            return;
        }

        Assert::fail($message ?? "Expected exception {$expectedExceptionClass} was not thrown");
    }
}
