<?php

namespace Tempest\Cache\Testing;

use Tempest\Cache\Cache;
use Tempest\Container\Container;
use Tempest\Container\DynamicInitializer;
use Tempest\Container\Singleton;
use Tempest\Discovery\SkipDiscovery;
use Tempest\Reflection\ClassReflector;
use UnitEnum;

#[SkipDiscovery]
final class RestrictedCacheInitializer implements DynamicInitializer
{
    public function canInitialize(ClassReflector $class, null|string|UnitEnum $tag): bool
    {
        return $class->getType()->matches(Cache::class);
    }

    #[Singleton]
    public function initialize(ClassReflector $class, null|string|UnitEnum $tag, Container $container): Cache
    {
        return new RestrictedCache($tag);
    }
}
