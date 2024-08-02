<?php

declare(strict_types=1);

namespace Tempest\Mapper;

use ReflectionClass;
use Tempest\Container\Container;
use Tempest\Discovery\Discovery;
use Tempest\Discovery\HandlesDiscoveryCache;

final readonly class MapperDiscovery implements Discovery
{
    use HandlesDiscoveryCache;

    public function __construct(
        private MapperConfig $config,
    ) {
    }

    public function discover(ReflectionClass $class): void
    {
        if (! $class->isInstantiable()) {
            return;
        }

        if ($class->implementsInterface(Mapper::class)) {
            $this->config->mappers[] = $class->getName();
        }
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
