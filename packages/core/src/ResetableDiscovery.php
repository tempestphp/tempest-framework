<?php

declare(strict_types=1);

namespace Tempest\Core;

use Tempest\Discovery\Discovery;
use Tempest\Discovery\DiscoveryLocation;
use Tempest\Discovery\IsDiscovery;
use Tempest\Reflection\ClassReflector;

final class ResetableDiscovery implements Discovery
{
    use IsDiscovery;

    public function __construct(
        private readonly ResetableContainer $resetableContainer,
    ) {}

    public function discover(DiscoveryLocation $location, ClassReflector $class): void
    {
        if ($class->implements(Resetable::class)) {
            $this->discoveryItems->add($location, $class->getName());
        }
    }

    public function apply(): void
    {
        foreach ($this->discoveryItems as $className) {
            $this->resetableContainer->add($className);
        }
    }
}
