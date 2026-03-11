<?php

declare(strict_types=1);

namespace Tempest\Discovery;

use Tempest\Reflection\ClassReflector;

final class DiscoveryDiscovery implements Discovery
{
    use IsDiscovery;

    public function __construct(
        private readonly Registry $registry,
    ) {}

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
        foreach ($this->discoveryItems as $className) {
            $this->registry->classes[] = $className;
        }
    }
}
