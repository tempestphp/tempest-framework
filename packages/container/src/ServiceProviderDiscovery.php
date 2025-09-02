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
final class ServiceProviderDiscovery implements Discovery
{
    use IsDiscovery;

    public function __construct(
        protected readonly Container $container,
    ) {}

    public function discover(DiscoveryLocation $location, ClassReflector $class): void
    {
        if (! $class->implements(ServiceProvider::class)) {
            return;
        }

        foreach ($class->getPublicMethods() as $method) {
            $singleton = $method->getAttribute(Singleton::class);

            $this->discoveryItems->add($location, [$method, isset($singleton), $singleton?->tag]);
        }
    }

    public function apply(): void
    {
        foreach ($this->discoveryItems as [$method, $singleton, $tag]) {
            $className = $method->getReturnType()->getName();
            $definition = static fn (Container $container) => $container->invoke($method);

            if ($singleton) {
                $this->container->singleton($className, $definition, $tag);
            } else {
                $this->container->register($className, $definition);
            }
        }
    }
}
