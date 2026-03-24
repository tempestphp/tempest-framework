<?php

namespace Tempest\Discovery\Tests\Fixtures;

use Tempest\Container\Container;
use Tempest\Discovery\SkipDiscovery;

#[SkipDiscovery(static function (Container $container): bool {
    return $container->get(DependencyForItemWithClosureSkip::class)->shouldSkip;
})]
final class ItemWithClosureSkip {}
