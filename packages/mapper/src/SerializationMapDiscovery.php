<?php

namespace Tempest\Mapper;

use Tempest\Discovery\Discovery;
use Tempest\Discovery\DiscoveryLocation;
use Tempest\Discovery\IsDiscovery;
use Tempest\Reflection\ClassReflector;

final class SerializationMapDiscovery implements Discovery
{
    use IsDiscovery;

    public function __construct(
        private readonly MapperConfig $mapperConfig,
    ) {}

    public function discover(DiscoveryLocation $location, ClassReflector $class): void
    {
        if ($attribute = $class->getAttribute(SerializeAs::class)) {
            $this->discoveryItems->add($location, [$class->getName(), $attribute->name]);
        }
    }

    public function apply(): void
    {
        foreach ($this->discoveryItems as [$className, $serializationName]) {
            $this->mapperConfig->serializeAs($className, $serializationName);
        }
    }
}
