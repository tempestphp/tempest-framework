<?php

namespace Tempest\Storage\Testing;

use Tempest\Container\Container;
use Tempest\Container\DynamicInitializer;
use Tempest\Container\Singleton;
use Tempest\Discovery\SkipDiscovery;
use Tempest\Reflection\ClassReflector;
use Tempest\Storage\Storage;

#[SkipDiscovery]
final class RestrictedStorageInitializer implements DynamicInitializer
{
    public function canInitialize(ClassReflector $class, ?string $tag): bool
    {
        return $class->getType()->matches(Storage::class);
    }

    #[Singleton]
    public function initialize(ClassReflector $class, ?string $tag, Container $container): Storage
    {
        return new RestrictedStorage($tag);
    }
}
