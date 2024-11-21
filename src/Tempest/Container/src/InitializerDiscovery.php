<?php

declare(strict_types=1);

namespace Tempest\Container;

use Tempest\Console\Highlight\ConsoleHighlighterInitializer;
use Tempest\Core\Discovery;
use Tempest\Core\DiscoveryLocation;
use Tempest\Core\IsDiscovery;
use Tempest\Reflection\ClassReflector;

/**
 * @property GenericContainer $container
 */
final class InitializerDiscovery implements Discovery
{
    use IsDiscovery;

    public function __construct(
        private readonly Container $container,
    ) {}

    public function discover(DiscoveryLocation $location, ClassReflector $class): void
    {
        if (! $class->implements(Initializer::class) && ! $class->implements(DynamicInitializer::class)) {
            return;
        }

        $this->discoveryItems->add($location, $class->getName());
    }

    public function apply(): void
    {
        foreach ($this->discoveryItems->flatten() as $className) {
            $this->container->addInitializer($className);
        }
    }
}
