<?php

namespace Tempest\Storage\Testing;

use Tempest\Container\Container;
use Tempest\Container\Initializer;
use Tempest\Container\Singleton;
use Tempest\Discovery\SkipDiscovery;
use Tempest\Storage\Storage;

#[SkipDiscovery]
final class RestrictedStorageInitializer implements Initializer
{
    #[Singleton]
    public function initialize(Container $container): Storage
    {
        return new RestrictedStorage();
    }
}
