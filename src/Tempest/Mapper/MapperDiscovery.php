<?php

declare(strict_types=1);

namespace Tempest\Mapper;

use Tempest\Container\Container;
use Tempest\Discovery\Discovery;
use Tempest\Discovery\HandlesDiscoveryCache;
use Tempest\Support\Reflection\ClassReflector;

final readonly class MapperDiscovery implements Discovery
{
    use HandlesDiscoveryCache;

    public function __construct(
        private MapperConfig $config,
    ) {
    }

    public function discover(ClassReflector $class): void
    {
        if (! $class->implements(Mapper::class)) {
            return;
        }

        $this->config->mappers[] = $class->getName();
    }

    public function createCachePayload(): string
    {
        return serialize($this->config->mappers);
    }

    public function restoreCachePayload(Container $container, string $payload): void
    {
        $mappers = unserialize($payload);

        $this->config->mappers = $mappers;
    }
}
