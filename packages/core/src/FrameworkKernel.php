<?php

declare(strict_types=1);

namespace Tempest\Core;

use Dotenv\Dotenv;
use ErrorException;
use RuntimeException;
use Tempest\Container\Container;
use Tempest\Container\GenericContainer;
use Tempest\Core\Exceptions\ExceptionProcessor;
use Tempest\Core\Kernel\FinishDeferredTasks;
use Tempest\Core\Kernel\LoadConfig;
use Tempest\Core\Kernel\RegisterEmergencyExceptionHandler;
use Tempest\Discovery\AutoloadDiscoveryLocations;
use Tempest\Discovery\BootDiscovery;
use Tempest\Discovery\Composer;
use Tempest\Discovery\DiscoveryCache;
use Tempest\Discovery\DiscoveryCacheInitializer;
use Tempest\Discovery\DiscoveryConfig;
use Tempest\Discovery\DiscoveryLocation;
use Tempest\EventBus\EventBus;
use Tempest\Process\GenericProcessExecutor;
use Tempest\Support\Filesystem;

final class FrameworkKernel implements Kernel
{
    public readonly Container $container;

    public string $internalStorage;

    public DiscoveryConfig $discoveryConfig;

    public function __construct(
        public string $root,
        /** @var DiscoveryLocation[] */
        private readonly array $discoveryLocations = [],
        ?Container $container = null,
        ?string $internalStorage = null,
        private readonly bool $longRunning = false,
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
        bool $longRunning = false,
    ): self {
        if (! defined('TEMPEST_START')) {
            define('TEMPEST_START', value: hrtime(as_number: true));
        }

        return new self(
            root: $root,
            discoveryLocations: $discoveryLocations,
            container: $container,
            internalStorage: $internalStorage,
            longRunning: $longRunning,
        )
            ->registerKernel()
            ->validateRoot()
            ->loadEnv()
            ->registerEmergencyExceptionHandler()
            ->registerShutdownFunction()
            ->registerInternalStorage()
            ->loadComposer()
            ->loadDiscoveryConfig()
            ->loadConfig()
            ->bootDiscovery()
            ->registerExceptionHandler()
            ->event(KernelEvent::BOOTED);
    }

    public function createContainer(): GenericContainer
    {
        $container = new GenericContainer();

        GenericContainer::setInstance($container);

        return $container;
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

    public function shutdown(int|string $status = ''): void
    {
        $this->event(KernelEvent::SHUTTING_DOWN)
            ->finishDeferredTasks();

        if ($this->longRunning) {
            $this
                ->event(KernelEvent::RESETTING)
                ->resetContainer()
                ->event(KernelEvent::RESET);
        }

        $this->event(KernelEvent::SHUTDOWN);

        if (! $this->longRunning) {
            exit($status);
        }
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

    public function loadDiscoveryConfig(): self
    {
        /** @var DiscoveryConfig $discoveryConfig */
        $discoveryConfig = $this->container->get(DiscoveryConfig::class);

        $discoveryConfig->locations = [
            ...$this->discoveryLocations,
            ...(new AutoloadDiscoveryLocations(
                rootPath: $this->root,
                composer: $this->container->get(Composer::class),
            ))(),
        ];

        $this->container->config($discoveryConfig);
        $this->discoveryConfig = $discoveryConfig;

        return $this;
    }

    public function loadConfig(): self
    {
        $this->container->addInitializer(ConfigCacheInitializer::class);

        $loadConfig = new LoadConfig(
            discoveryConfig: $this->container->get(DiscoveryConfig::class),
            container: $this->container,
            cache: $this->container->get(ConfigCache::class),
            environment: Environment::guessFromEnvironment(),
        );

        $loadConfig();

        return $this;
    }

    public function bootDiscovery(): self
    {
        $this->container->addInitializer(DiscoveryCacheInitializer::class);

        $bootDiscovery = new BootDiscovery(
            container: $this->container,
            config: $this->container->get(DiscoveryConfig::class),
            cache: $this->container->get(DiscoveryCache::class),
        );

        $bootDiscovery();

        return $this;
    }

    public function registerInternalStorage(): self
    {
        $path = $this->internalStorage ?? $this->root . '/.tempest';

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

    public function resetContainer(): self
    {
        $this->container->reset();

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
        $environment = Environment::guessFromEnvironment();

        // During tests, PHPUnit registers its own error handling.
        if ($environment->isTesting()) {
            return $this;
        }

        // In development, we want to register a developer-friendly error
        // handler as soon as possible to catch any kind of exception.
        if (PHP_SAPI !== 'cli' && $environment->isLocal()) {
            new RegisterEmergencyExceptionHandler()->register();
        }

        return $this;
    }

    public function registerExceptionHandler(): self
    {
        // During tests, PHPUnit registers its own error handling.
        if (Environment::guessFromEnvironment()->isTesting()) {
            return $this;
        }

        $handler = $this->container->get(ExceptionHandler::class);

        ini_set('display_errors', 'Off'); // @mago-expect lint:no-ini-set
        set_exception_handler($handler->handle(...));
        set_error_handler(
            callback: function (int $code, string $message, string $filename, int $line): bool {
                // if error_reporting is 0, the error was silenced with @
                if ((error_reporting() & $code) === 0) {
                    return false;
                }

                $exception = new ErrorException(
                    message: $message,
                    code: 0,
                    severity: $code,
                    filename: $filename,
                    line: $line,
                );

                // warnings/notices/deprecations get reported but don't throw
                if (($code & (E_WARNING | E_USER_WARNING | E_NOTICE | E_USER_NOTICE | E_DEPRECATED | E_USER_DEPRECATED)) !== 0) {
                    $this->container->get(ExceptionProcessor::class)->process($exception);
                    return true;
                }

                throw $exception;
            },
            error_levels: E_ALL,
        );

        return $this;
    }
}
