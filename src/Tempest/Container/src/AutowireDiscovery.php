<?php

declare(strict_types=1);

namespace Tempest\Container;

use Tempest\Core\Discovery;
use Tempest\Core\HandlesDiscoveryCache;
use Tempest\Reflection\ClassReflector;

/**
 * @property GenericContainer $container
 */
final class AutowireDiscovery implements Discovery
{
    use HandlesDiscoveryCache;
    /** @var array<string, string> */
    private array $trackedAutowireDefinitions = [];

    public function __construct(
        private readonly Container $container,
    ) {
    }

    public function discover(ClassReflector $class): void
    {
        $autowire = $class->getAttribute(Autowire::class);

        if ($autowire === null) {
            return;
        }

        if ($class->getAttribute(Singleton::class) !== null) {
            $this->discoverAsSingleton($class);
        } else {
            $this->discoverAsDefinition($class);
        }
    }

    private function discoverAsSingleton(ClassReflector $class): void
    {
        $interfaces = $class->getReflection()->getInterfaceNames();
        foreach ($interfaces as $interface) {
            // No need to track this autowire definition as it will be cached as a singleton

            $this->container->singleton(
                $interface,
                static fn (Container $container) => $container->get($class->getName())
            );
        }
    }

    private function discoverAsDefinition(ClassReflector $class): void
    {
        $interfaces = $class->getReflection()->getInterfaceNames();
        foreach ($interfaces as $interface) {
            $this->trackedAutowireDefinitions[$interface] = $class->getName();

            $this->container->register(
                $interface,
                static fn (Container $container) => $container->get($class->getName())
            );
        }
    }

    public function createCachePayload(): string
    {
        return serialize($this->trackedAutowireDefinitions);
    }

    public function restoreCachePayload(Container $container, string $payload): void
    {
        $this->trackedAutowireDefinitions = unserialize($payload, ['allowed_classes' => false]);

        foreach ($this->trackedAutowireDefinitions as $interface => $className) {
            $this->container->register($interface, static fn (Container $container) => $container->get($className));
        }
    }
}
