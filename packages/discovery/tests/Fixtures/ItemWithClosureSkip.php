<?php

namespace Tempest\Discovery\Tests\Fixtures;

use Psr\Container\ContainerInterface;
use Tempest\Discovery\SkipDiscovery;

#[SkipDiscovery(static function (ContainerInterface $container): bool {
    if (! $container->has(DependencyForItemWithClosureSkip::class)) {
        return false;
    }

    return $container->get(DependencyForItemWithClosureSkip::class)->shouldSkip;
})]
final class ItemWithClosureSkip {}
