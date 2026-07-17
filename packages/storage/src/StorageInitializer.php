<?php

namespace Tempest\Storage;

use Tempest\Container\Container;
use Tempest\Container\DynamicInitializer;
use Tempest\Container\Singleton;
use Tempest\Reflection\ClassReflector;
use Tempest\Storage\Config\StorageConfig;
use UnitEnum;

final class StorageInitializer implements DynamicInitializer
{
    public function canInitialize(ClassReflector $class, string|UnitEnum|null $tag): bool
    {
        return $class->getType()->matches(Storage::class);
    }

    #[Singleton]
    public function initialize(ClassReflector $class, string|UnitEnum|null $tag, Container $container): Storage
    {
        return new GenericStorage(
            storageConfig: $container->get(StorageConfig::class, $tag),
        );
    }
}
