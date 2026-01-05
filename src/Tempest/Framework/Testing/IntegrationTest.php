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
use Tempest\Core\ExceptionTester;
use Tempest\Core\FrameworkKernel;
use Tempest\Core\Kernel;
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

use function Tempest\env;
use function Tempest\Support\Path\normalize;
use function Tempest\Support\Path\to_absolute_path;

/**
 * @mago-expect lint:too-many-properties
 */
abstract class IntegrationTest extends TestCase
{
    protected string $root;

    protected string $internalStorage;

    /** @var \Tempest\Discovery\DiscoveryLocation[] */
    protected array $discoveryLocations = [];

    protected Kernel $kernel;

    protected GenericContainer $container;

    protected ConsoleTester $console;

    /**
     * Provides utilities for testing HTTP routes.
     */
    protected HttpRouterTester $http;

    /**
     * Provides utilities for testing installers.
     */
    protected InstallerTester $installer;

    /**
     * Provides utilities for testing the Vite integration.
     */
    protected ViteTester $vite;

    /**
     * Provides utilities for testing the event bus.
     */
    protected EventBusTester $eventBus;

    /**
     * Provides utilities for testing storage management.
     */
    protected StorageTester $storage;

    /**
     * Provides utilities for testing emails.
     */
    protected MailTester $mailer;

    /**
     * Provides utilities for testing the cache.
     */
    protected CacheTester $cache;

    /**
     * Provides utilities for testing exception reporting.
     */
    protected ExceptionTester $exceptions;

    /**
     * Provides utilities for testing process execution.
     */
    protected ProcessTester $process;

    /**
     * Provides utilities for testing OAuth flows.
     */
    protected OAuthTester $oauth;

    /**
     * Provides utilities for testing the database.
     */
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
        $this->internalStorage = $this->root . '/.tempest/test_internal_storage/' . env('TEST_TOKEN', 'default');

        $discoveryLocations = [...$this->discoveryLocations, ...$this->discoverTestLocations()];

        $this->kernel ??= FrameworkKernel::boot(
            root: $this->root,
            discoveryLocations: $discoveryLocations,
            internalStorage: $this->internalStorage,
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
        unset($this->kernel);
        /** @phpstan-ignore-next-line */
        unset($this->container);
        /** @phpstan-ignore-next-line */
        unset($this->console);
        /** @phpstan-ignore-next-line */
        unset($this->http);
        /** @phpstan-ignore-next-line */
        unset($this->oauth);

        GenericContainer::setInstance(null);
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
