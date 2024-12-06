<?php

declare(strict_types=1);

namespace Tempest\Core;

use Tempest\Reflection\ClassReflector;

final class DiscoveryDiscovery implements Discovery
{
    use IsDiscovery;

    public function __construct(
        private readonly Kernel $kernel,
    ) {
    }

    public function discover(DiscoveryLocation $location, ClassReflector $class): void
    {
        return;

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
        foreach ($this->discoveryItems as $className) {
            $this->kernel->discoveryClasses[] = $className;
        }
    }
}
