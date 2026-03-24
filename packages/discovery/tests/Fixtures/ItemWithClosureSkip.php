<?php

namespace Tempest\Discovery\Tests\Fixtures;

use Psr\Container\ContainerInterface;
use Tempest\Discovery\SkipDiscovery;
use Throwable;

#[SkipDiscovery(static function (ContainerInterface $container): bool {
    if (! $container->has(DependencyForItemWithClosureSkip::class)) {
        return true;
    }

    try {
        return $container->get(DependencyForItemWithClosureSkip::class)->shouldSkip;
    } catch (Throwable) {
        return true;
    }
})]
final class ItemWithClosureSkip {}
