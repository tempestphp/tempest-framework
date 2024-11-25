<?php

declare(strict_types=1);

namespace Tempest\Mapper;

use Tempest\Core\Discovery;
use Tempest\Core\DiscoveryLocation;
use Tempest\Core\IsDiscovery;
use Tempest\Reflection\ClassReflector;

final class MapperDiscovery implements Discovery
{
    use IsDiscovery;

    public function __construct(
        private readonly MapperConfig $config,
    ) {
    }

    public function discover(DiscoveryLocation $location, ClassReflector $class): void
    {
        if (! $class->implements(Mapper::class)) {
            return;
        }

        $this->discoveryItems->add($location, $class->getName());
    }

    public function apply(): void
    {
        foreach ($this->discoveryItems as $className) {
            $this->config->mappers[] = $className;
        }
    }
}
