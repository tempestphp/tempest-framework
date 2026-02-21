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

    private const int IS_RESETABLE = 1;
    private const int IS_RESETABLE_STATIC = 2;

    public function __construct(
        private readonly ResetableContainer $resetableContainer,
    ) {}

    public function discover(DiscoveryLocation $location, ClassReflector $class): void
    {
        if ($class->implements(Resetable::class)) {
            $this->discoveryItems->add($location, [self::IS_RESETABLE, $class->getName()]);
        }

        if ($class->implements(ResetableStatic::class)) {
            $this->discoveryItems->add($location, [self::IS_RESETABLE_STATIC, $class->getName()]);
        }
    }

    public function apply(): void
    {
        foreach ($this->discoveryItems as [$type, $className]) {
            if ($type === self::IS_RESETABLE) {
                $this->resetableContainer->add($className);
            } else {
                $this->resetableContainer->addStatic($className);
            }
        }
    }
}
