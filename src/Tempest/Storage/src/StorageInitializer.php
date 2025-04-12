<?php

namespace Tempest\Storage;

use Tempest\Container\Container;
use Tempest\Container\Initializer;
use Tempest\Storage\Config\StorageConfig;

final class StorageInitializer implements Initializer
{
    public function initialize(Container $container): Storage
    {
        return new GenericStorage(
            storageConfig: $container->get(StorageConfig::class), // TODO(innocenzi): tag
        );
    }
}
