<?php

declare(strict_types=1);

namespace Tempest\Mapper;

use Tempest\Container\Container;
use Tempest\Core\Discovery;
use Tempest\Core\HandlesDiscoveryCache;
use Tempest\Reflection\ClassReflector;

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
        $mappers = unserialize($payload, ['allowed_classes' => [Mapper::class]]);

        $this->config->mappers = $mappers;
    }
}
