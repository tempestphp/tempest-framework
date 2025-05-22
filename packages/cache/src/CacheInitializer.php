<?php

declare(strict_types=1);

namespace Tempest\Cache;

use Tempest\Cache\Config\CacheConfig;
use Tempest\Container\Container;
use Tempest\Container\DynamicInitializer;
use Tempest\Container\Singleton;
use Tempest\Reflection\ClassReflector;
use UnitEnum;

use function Tempest\env;
use function Tempest\Support\str;

final readonly class CacheInitializer implements DynamicInitializer
{
    public function canInitialize(ClassReflector $class, null|string|UnitEnum $tag): bool
    {
        return $class->getType()->matches(Cache::class);
    }

    #[Singleton]
    public function initialize(ClassReflector $class, null|string|UnitEnum $tag, Container $container): Cache
    {
        return new GenericCache(
            cacheConfig: $container->get(CacheConfig::class, $tag),
            enabled: $this->shouldCacheBeEnabled($tag),
        );
    }

    private function shouldCacheBeEnabled(?string $tag): bool
    {
        $globalCacheEnabled = (bool) env('CACHE_ENABLED', default: true);

        if (! $tag) {
            return $globalCacheEnabled;
        }

        $environmentVariableName = str($tag)
            ->snake()
            ->upper()
            ->prepend('CACHE_')
            ->append('_ENABLED')
            ->toString();

        return (bool) env($environmentVariableName, default: $globalCacheEnabled);
    }
}
