<?php

declare(strict_types=1);

namespace Tempest\Core;

use Tempest\Container\Container;
use Tempest\Container\GenericContainer;
use Tempest\Core\Kernel\LoadConfig;
use Tempest\Core\Kernel\LoadDiscoveryClasses;
use Tempest\Core\Kernel\LoadDiscoveryLocations;
use Tempest\EventBus\EventBus;

final class Kernel
{
    public readonly Container $container;

    public array $discoveryClasses = [
        DiscoveryDiscovery::class,
    ];

    public function __construct(
        public readonly string $root,
        ?Container $container = null,
        public array $discoveryLocations = [],
        public bool $discoveryCache = false,
    ) {
        $this->container = $container ?? $this->createContainer();

        $this
            ->registerKernel()
            ->loadDiscoveryLocations()
            ->loadConfig()
            ->loadDiscovery();

        $this->container->get(EventBus::class)->dispatch(KernelEvent::BOOTED);
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
