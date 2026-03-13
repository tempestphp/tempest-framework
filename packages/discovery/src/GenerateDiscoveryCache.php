<?php

namespace Tempest\Discovery;

use Psr\Container\ContainerInterface;

final class GenerateDiscoveryCache
{
    public function __invoke(
        ContainerInterface $container,
        DiscoveryConfig $config,
        DiscoveryCache $cache,
    ): void {
        $bootDiscovery = new BootDiscovery(
            container: $container,
            config: $config,
            cache: $cache->withStrategy(DiscoveryCacheStrategy::NONE),
        );

        $discoveries = $bootDiscovery->build();

        foreach ($config->locations as $location) {
            $cache->store($location, $discoveries);
        }

        $cache->storeStrategy($cache->strategy);
    }
}
