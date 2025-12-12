<?php

declare(strict_types=1);

namespace Tempest\Core;

use Dotenv\Dotenv;
use ErrorException;
use RuntimeException;
use Tempest\Console\Exceptions\ConsoleExceptionHandler;
use Tempest\Container\Container;
use Tempest\Container\GenericContainer;
use Tempest\Core\Kernel\FinishDeferredTasks;
use Tempest\Core\Kernel\LoadConfig;
use Tempest\Core\Kernel\LoadDiscoveryClasses;
use Tempest\Core\Kernel\LoadDiscoveryLocations;
use Tempest\Core\Kernel\RegisterEmergencyExceptionHandler;
use Tempest\EventBus\EventBus;
use Tempest\Process\GenericProcessExecutor;
use Tempest\Router\Exceptions\HttpExceptionHandler;
use Tempest\Support\Filesystem;

final class FrameworkKernel implements Kernel
{
    public readonly Container $container;

    public bool $discoveryCache;

    public array $discoveryClasses = [];

    public string $internalStorage;

    public function __construct(
        public string $root,
        /** @var \Tempest\Discovery\DiscoveryLocation[] $discoveryLocations */
        public array $discoveryLocations = [],
        ?Container $container = null,
        ?string $internalStorage = null,
    ) {
        $this->container = $container ?? $this->createContainer();

        if ($internalStorage !== null) {
            $this->internalStorage = $internalStorage;
        }
    }

    public static function boot(
        string $root,
        array $discoveryLocations = [],
        ?Container $container = null,
        ?string $internalStorage = null,
    ): self {
        if (! defined('TEMPEST_START')) {
            define('TEMPEST_START', value: hrtime(true));
        }

        return new self(
            root: $root,
            discoveryLocations: $discoveryLocations,
            container: $container,
            internalStorage: $internalStorage,
        )
            ->validateRoot()
            ->loadEnv()
            ->registerEmergencyExceptionHandler()
            ->registerShutdownFunction()
            ->registerInternalStorage()
            ->registerKernel()
            ->loadComposer()
            ->loadDiscoveryLocations()
            ->loadConfig()
            ->loadDiscovery()
            ->registerExceptionHandler()
            ->event(KernelEvent::BOOTED);
    }

    public function validateRoot(): self
    {
        $root = Filesystem\normalize_path($this->root);

        if (! is_dir($root)) {
            throw new RuntimeException('The specified root directory is not valid.');
        }

        $this->root = $root;

        return $this;
    }

    public function shutdown(int|string $status = ''): never
    {
        $this->finishDeferredTasks()
            ->event(KernelEvent::SHUTDOWN);

        exit($status);
    }

    public function createContainer(): Container
    {
        $container = new GenericContainer();

        GenericContainer::setInstance($container);

        $container->singleton(Container::class, fn () => $container);

        return $container;
    }

    public function loadComposer(): self
    {
        if (class_exists(GenericProcessExecutor::class)) {
            $processExecutor = new GenericProcessExecutor();
        } else {
            $processExecutor = null;
        }

        $composer = new Composer(
            root: $this->root,
            executor: $processExecutor,
        )->load();

        $this->container->singleton(Composer::class, $composer);

        return $this;
    }

    public function loadEnv(): self
    {
        $dotenv = Dotenv::createUnsafeImmutable($this->root);
        $dotenv->safeLoad();

        return $this;
    }

    public function registerKernel(): self
    {
        $this->container->singleton(Kernel::class, $this);
        $this->container->singleton(self::class, $this);

        return $this;
    }

    public function registerShutdownFunction(): self
    {
        // Fix for classes that don't have a proper PSR-4 namespace,
        // they break discovery with an unrecoverable error,
        // but you don't know why because PHP simply says "duplicate classname" instead of something reasonable.
        register_shutdown_function(function (): void {
            $error = error_get_last();

            $message = $error['message'] ?? '';

            if (str_contains($message, 'Cannot declare class')) {
                echo 'Does this class have the right namespace?' . PHP_EOL;
            }
        });

        return $this;
    }

    public function loadDiscoveryLocations(): self
    {
        $this->container->invoke(LoadDiscoveryLocations::class);

        return $this;
    }

    public function loadDiscovery(): self
    {
        $this->container->addInitializer(DiscoveryCacheInitializer::class);
        $this->container->invoke(
            LoadDiscoveryClasses::class,
            discoveryLocations: $this->discoveryLocations,
        );

        return $this;
    }

    public function loadConfig(): self
    {
        $this->container->addInitializer(ConfigCacheInitializer::class);
        $this->container->invoke(LoadConfig::class);

        return $this;
    }

    public function registerInternalStorage(): self
    {
        $path = isset($this->internalStorage) ? $this->internalStorage : $this->root . '/.tempest';

        if (! is_dir($path)) {
            if (file_exists($path)) {
                throw CouldNotRegisterInternalStorage::fileExists($path);
            }

            if (! mkdir($path, recursive: true)) {
                throw CouldNotRegisterInternalStorage::directoryNotWritable($path);
            }
        } elseif (! is_writable($path)) {
            throw CouldNotRegisterInternalStorage::noPermission($path);
        }

        $this->internalStorage = Filesystem\normalize_path($path);

        return $this;
    }

    public function finishDeferredTasks(): self
    {
        $this->container->invoke(FinishDeferredTasks::class);

        return $this;
    }

    public function event(object $event): self
    {
        if (interface_exists(EventBus::class)) {
            $this->container->get(EventBus::class)->dispatch($event);
        }

        return $this;
    }

    public function registerEmergencyExceptionHandler(): self
    {
        $environment = Environment::fromEnv();

        // During tests, PHPUnit registers its own error handling.
        if ($environment->isTesting()) {
            return $this;
        }

        // In development, we want to register a developer-friendly error
        // handler as soon as possible to catch any kind of exception.
        if (PHP_SAPI !== 'cli' && ! $environment->isProduction()) {
            new RegisterEmergencyExceptionHandler()->register();
        }

        return $this;
    }

    public function registerExceptionHandler(): self
    {
        $appConfig = $this->container->get(AppConfig::class);

        // During tests, PHPUnit registers its own error handling.
        if ($appConfig->environment->isTesting()) {
            return $this;
        }

        // TODO: refactor to not have a hard-coded dependency on these exception handlers
        if (! class_exists(ConsoleExceptionHandler::class) || ! class_exists(HttpExceptionHandler::class)) {
            return $this;
        }

        $handler = $this->container->get(ExceptionHandler::class);

        set_exception_handler($handler->handle(...));
        set_error_handler(function (int $code, string $message, string $filename, int $line) use ($handler): bool {
            $handler->handle(new ErrorException(
                message: $message,
                code: $code,
                filename: $filename,
                line: $line,
            ));

            return true;
        }, error_levels: E_ERROR);

        return $this;
    }
}
