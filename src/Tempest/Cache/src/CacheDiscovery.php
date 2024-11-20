<?php

declare(strict_types=1);

namespace Tempest\Cache;

use Tempest\Container\Container;
use Tempest\Core\Discovery;
use Tempest\Core\DiscoveryLocation;
use Tempest\Core\IsDiscovery;
use Tempest\Reflection\ClassReflector;

final readonly class CacheDiscovery implements Discovery
{
    use IsDiscovery;

    public function __construct(
        private CacheConfig $cacheConfig,
    ) {
    }

    public function discover(DiscoveryLocation $location, ClassReflector $class): void
    {
        if ($class->implements(Cache::class)) {
            $this->discoveryItems->add($location, $class->getName());
        }
    }

    public function apply(): void
    {
        foreach ($this->discoveryItems->flatten() as $className) {
            $this->cacheConfig->addCache($className);
        }
    }
}
