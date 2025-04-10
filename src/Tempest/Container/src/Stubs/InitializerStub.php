<?php

declare(strict_types=1);

namespace Tempest\Container\Stubs;

use Tempest\Container\Container;
use Tempest\Container\Initializer;
use Tempest\Discovery\SkipDiscovery;

#[SkipDiscovery]
final class InitializerStub implements Initializer
{
    public function initialize(Container $container): mixed
    {
        return null;
    }
}
