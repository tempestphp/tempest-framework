<?php

declare(strict_types=1);

namespace Tempest\Core;

use Dotenv\Dotenv;
use Tempest\Container\Container;
use Tempest\Container\GenericContainer;
use Tempest\Core\Kernel\LoadConfig;
use Tempest\Core\Kernel\LoadDiscoveryClasses;
use Tempest\Core\Kernel\LoadDiscoveryLocations;
use function Tempest\env;
use Tempest\EventBus\EventBus;

final class Kernel
{
    public readonly Container $container;

    public bool $discoveryCache;

    public array $discoveryClasses = [
        DiscoveryDiscovery::class,
    ];

    public function __construct(
        public readonly string $root,
        public array $discoveryLocations = [],
        ?bool $discoveryCache = null,
        ?Container $container = null,
    ) {
        $this->container = $container ?? $this->createContainer();

        $this
            ->loadEnv()
            ->setDiscoveryCache($discoveryCache)
            ->registerShutdownFunction()
            ->registerKernel()
            ->loadDiscoveryLocations()
            ->loadConfig()
            ->loadExceptionHandler()
            ->loadDiscovery()
            ->event(KernelEvent::BOOTED);
    }

    public static function boot(string $root, ?Container $container = null): self
    {
        return new self(
            root: $root,
            container: $container,
        );
    }

    private function createContainer(): Container
    {
        $container = new GenericContainer();

        GenericContainer::setInstance($container);

        $container->singleton(Container::class, fn () => $container);

        return $container;
    }

    private function loadEnv(): self
    {
        $dotenv = Dotenv::createUnsafeImmutable($this->root);
        $dotenv->safeLoad();

        return $this;
    }

    private function setDiscoveryCache(?bool $discoveryCache): self
    {
        if ($discoveryCache !== null) {
            $this->discoveryCache = $discoveryCache;
        } else {
            $this->discoveryCache = env('DISCOVERY_CACHE', false);
        }

        return $this;
    }

    private function registerKernel(): self
    {
        $this->container->singleton(self::class, $this);

        return $this;
    }

    private function registerShutdownFunction(): self
    {
        // Fix for classes that don't have a proper PSR-4 namespace,
        // they break discovery with an unrecoverable error,
        // but you don't know why because PHP simply says "duplicate classname" instead of something reasonable.
        register_shutdown_function(function (): void {
            $error = error_get_last();

            $message = $error['message'] ?? '';

            if (str_contains($message, 'Cannot declare class')) {
                echo "Does this class have the right namespace?" . PHP_EOL;
            }
        });

        return $this;
    }

    private function loadDiscoveryLocations(): self
    {
        ($this->container->get(LoadDiscoveryLocations::class))();

        return $this;
    }

    private function loadDiscovery(): self
    {
        ($this->container->get(LoadDiscoveryClasses::class))();

        return $this;
    }

    private function loadConfig(): self
    {
        $this->container->get(LoadConfig::class)();

        return $this;
    }

    private function loadExceptionHandler(): self
    {
        $appConfig = $this->container->get(AppConfig::class);

        $appConfig->exceptionHandlerSetup->setup($appConfig);

        return $this;
    }

    private function event(object $event): self
    {
        if (interface_exists(EventBus::class)) {
            $this->container->get(EventBus::class)->dispatch($event);
        }

        return $this;
    }
}
