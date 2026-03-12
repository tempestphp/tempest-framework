<?php

declare(strict_types=1);

namespace Tempest\Discovery;

use Tempest\Reflection\ClassReflector;

final class DiscoveryDiscovery implements Discovery
{
    use IsDiscovery;

    private ?Registry $registry = null;

    public function setRegistry(Registry $registry): self
    {
        $this->registry = $registry;

        return $this;
    }

    public function discover(DiscoveryLocation $location, ClassReflector $class): void
    {
        if ($class->getName() === self::class) {
            return;
        }

        if (! $class->implements(Discovery::class)) {
            return;
        }

        $this->discoveryItems->add($location, $class->getName());
    }

    public function apply(): void
    {
        if ($this->registry === null) {
            throw new RegistryWasNotSet();
        }

        foreach ($this->discoveryItems as $className) {
            $this->registry->classes[] = $className;
        }
    }
}
