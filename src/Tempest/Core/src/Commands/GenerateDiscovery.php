<?php

declare(strict_types=1);

namespace Tempest\Core\Commands;

use Tempest\Cache\DiscoveryCacheStrategy;
use Tempest\Container\Container;
use Tempest\Container\GenericContainer;
use Tempest\Core\DiscoveryCache;
use Tempest\Core\Kernel;
use Tempest\Core\Kernel\LoadDiscoveryClasses;
use function Tempest\env;

final readonly class GenerateDiscovery
{
    public function __construct(
        private Kernel $kernel,
        private DiscoveryCache $discoveryCache,
    ) {
    }

    public function __invoke(): void
    {
        $strategy = $this->resolveDiscoveryCacheStrategy();

        $kernel = $this->resolveKernel();

        $loadDiscoveryClasses = new LoadDiscoveryClasses(
            kernel: $kernel,
            container: $kernel->container,
            discoveryCache: $this->discoveryCache,
        );

        $discoveries = $loadDiscoveryClasses->build();

        foreach ($discoveries as $discovery) {
            $discoveryItems = $discovery->getItems();

            if ($strategy === DiscoveryCacheStrategy::PARTIAL) {
                $discoveryItems = $discoveryItems->onlyVendor();
            }

            $this->discoveryCache->store($discovery, $discoveryItems);
        }

        $this->discoveryCache->storeStrategy($strategy);
    }

    private function resolveDiscoveryCacheStrategy(): DiscoveryCacheStrategy
    {
        $cache = env('CACHE');

        if ($cache !== null) {
            return DiscoveryCacheStrategy::make($cache);
        }

        return DiscoveryCacheStrategy::make(env('DISCOVERY_CACHE'));
    }

    public function resolveKernel(): Kernel
    {
        $container = new GenericContainer();
        $container->singleton(Container::class, $container);

        return (new Kernel(
            root: $this->kernel->root,
            discoveryLocations: $this->kernel->discoveryLocations,
            container: $container,
        ))
            ->registerKernel()
            ->loadComposer()
            ->loadConfig();
    }
}
