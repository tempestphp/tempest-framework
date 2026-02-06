<?php

declare(strict_types=1);

namespace Tempest\Core;

use Tempest\Container\Container;

final readonly class Tempest
{
    /** @param \Tempest\Discovery\DiscoveryLocation[] $discoveryLocations */
    public static function boot(?string $root = null, array $discoveryLocations = [], ?string $internalStorage = null): Container
    {
        $kernel = FrameworkKernel::boot(
            root: $root ?? getcwd(),
            discoveryLocations: $discoveryLocations,
            internalStorage: $internalStorage,
        );

        return $kernel->container;
    }
}
