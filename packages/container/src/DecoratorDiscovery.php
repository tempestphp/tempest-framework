<?php

declare(strict_types=1);

namespace Tempest\Container;

use Tempest\Discovery\Discovery;
use Tempest\Discovery\DiscoveryLocation;
use Tempest\Discovery\IsDiscovery;
use Tempest\Reflection\ClassReflector;

/**
 * @property GenericContainer $container
 */
final class DecoratorDiscovery implements Discovery
{
    use IsDiscovery;

    public function __construct(
        private readonly Container $container,
    ) {}

    public function discover(DiscoveryLocation $location, ClassReflector $class): void
    {
        $decorator = $class->getAttribute(Decorator::class);

        if ($decorator === null) {
            return;
        }

        $this->discoveryItems->add($location, [$class, $decorator]);
    }

    public function apply(): void
    {
        foreach ($this->discoveryItems as [$class, $decorator]) {
            /** @var Decorator $decorator */
            $this->container->addDecorator($class, $decorator->decorates);
        }
    }
}
