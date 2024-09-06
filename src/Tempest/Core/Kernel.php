<?php

declare(strict_types=1);

namespace Tempest\Core;

use Tempest\Container\Container;
use Tempest\Container\GenericContainer;
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
            ->initDiscovery()
            ->resolveEventBus()
            ->init();
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

    private function initDiscovery(): self
    {
        $this->discoveryLocations = $this->container->get(LoadDiscoveryLocations::class)();

        $this->discoveryClasses = $this->container->get(LoadDiscoveryClasses::class)();

        return $this;
    }

    private function init(): self
    {
        $this->eventBus->dispatch(KernelEvent::BOOTED);

        return $this;
    }

//
//    public function init(): Container
//    {
//        $container = $this->createContainer();
//
//        $bootstraps = [
//            DiscoveryLocationBootstrap::class,
//            ConfigBootstrap::class,
//            DiscoveryBootstrap::class,
//        ];
//
//        foreach ($bootstraps as $bootstrap) {
//            $container->get(
//                $bootstrap,
//                kernel: $this,
//                appConfig: $this->appConfig,
//            )->boot();
//        }
//
//        return $container;
//    }


}
