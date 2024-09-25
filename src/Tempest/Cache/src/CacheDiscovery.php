<?php

namespace Tempest\Cache;

use Tempest\Container\Container;
use Tempest\Core\Discovery;
use Tempest\Core\HandlesDiscoveryCache;
use Tempest\Reflection\ClassReflector;

final readonly class CacheDiscovery implements Discovery
{
    use HandlesDiscoveryCache;

    public function __construct(
        private CacheConfig $cacheConfig,
    ) {}

    public function discover(ClassReflector $class): void
    {
        if ($class->implements(Cache::class)) {
            $this->cacheConfig->addCache($class->getName());
        }
    }

    public function createCachePayload(): string
    {
        return serialize($this->cacheConfig->caches);
    }

    public function restoreCachePayload(Container $container, string $payload): void
    {
        $this->cacheConfig->caches = unserialize($payload);
    }
}