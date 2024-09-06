<?php

declare(strict_types=1);

namespace Tempest\Core;

use Tempest\Container\Container;
use Tempest\Container\GenericContainer;
use Tempest\Core\Bootstraps\LoadConfig;
use Tempest\Core\Bootstraps\LoadDiscoveryClasses;
use Tempest\Core\Bootstraps\LoadDiscoveryLocations;
use Tempest\EventBus\EventBus;

final class Kernel
{
    public readonly string $root;

    public readonly Container $container;

    public readonly EventBus $eventBus;

    public array $discoveryLocations = [];

    public array $discoveryClasses = [
        DiscoveryDiscovery::class,
    ];

    public bool $discoveryCache = false;

    public function __construct(
        string $root,
        ?Container $container = null,
        array $discoveryLocations = [],
        bool $discoveryCache = false,
    ) {
        $this->root = $root;
        $this->container = $container ?? $this->createContainer();
        $this->discoveryLocations = $discoveryLocations;
        $this->discoveryCache = $discoveryCache;

        $this
            ->registerKernel()
            ->loadDiscoveryLocations()
            ->loadConfig()
            ->loadDiscovery()
            ->resolveEventBus();

        $this->eventBus->dispatch(KernelEvent::BOOTED);
    }

    public static function boot(string $root, ?Container $container = null): self
    {
        return new self(
            root: $root,
            container: $container,
        );
    }

    private function registerKernel(): self
    {
        $this->container->singleton(self::class, $this);

        return $this;
    }

    private function resolveEventBus(): self
    {
        $this->eventBus = $this->container->get(EventBus::class);

        return $this;
    }

    private function createContainer(): Container
    {
        $container = new GenericContainer();

        GenericContainer::setInstance($container);

        $container->singleton(Container::class, fn () => $container);

        return $container;
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
}
