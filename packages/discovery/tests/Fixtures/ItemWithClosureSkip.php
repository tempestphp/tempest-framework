<?php

namespace Tempest\Discovery\Tests\Fixtures;

use Psr\Container\ContainerInterface;
use Tempest\Discovery\SkipDiscovery;

#[SkipDiscovery(static function (ContainerInterface $container): bool {
    return $container->get(DependencyForItemWithClosureSkip::class)?->shouldSkip ?? false;
})]
final class ItemWithClosureSkip {}
