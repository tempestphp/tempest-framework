<?php

namespace Tempest\Generation\TypeScript;

use Tempest\Discovery\Discovery;
use Tempest\Discovery\DiscoveryLocation;
use Tempest\Discovery\IsDiscovery;
use Tempest\Reflection\ClassReflector;
use UnitEnum;

final class TypeSourceDiscovery implements Discovery
{
    use IsDiscovery;

    public function __construct(
        private TypeScriptGenerationConfig $config,
    ) {}

    public function discover(DiscoveryLocation $location, ClassReflector $class): void
    {
        if ($class->getAttribute(AsType::class)) {
            $this->discoveryItems->add($location, [$class->getName()]);
        }

        // TODO(innocenzi): other heuristics for implicit opt-in
        // eg. automatically convert DTOs, excluding vendor ones

        if ($location->isVendor()) {
            return;
        }

        if ($class->implements(UnitEnum::class)) {
            $this->discoveryItems->add($location, [$class->getName()]);
        }
    }

    public function apply(): void
    {
        foreach ($this->discoveryItems as [$className]) {
            $this->config->sources[] = $className;
        }
    }
}
