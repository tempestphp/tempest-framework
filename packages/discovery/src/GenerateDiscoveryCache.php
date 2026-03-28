<?php

namespace Tempest\Discovery;

use Psr\Container\ContainerInterface;

final class GenerateDiscoveryCache
{
    /**
     * @param class-string<Discovery>[]|null $discoveryClasses
     * @param DiscoveryLocation[]|null $discoveryLocations
     * @param Discovery[]|null $discoveries
     */
    public function __invoke(
        ContainerInterface $container,
        DiscoveryConfig $config,
        DiscoveryCache $cache,
        ?array $discoveryClasses = null,
        ?array $discoveryLocations = null,
        ?array $discoveries = null,
    ): void {
        $originalStrategy = $cache->strategy;
        $cache = $cache->withStrategy(DiscoveryCacheStrategy::NONE);

        $discoveries ??= new BootDiscovery(
            container: $container,
            config: $config,
            cache: $cache,
        )->build($discoveryClasses, $discoveryLocations);

        foreach ($config->locations as $location) {
            $cache->store($location, $discoveries);
        }

        $cache->storeStrategy($originalStrategy);
    }
}
