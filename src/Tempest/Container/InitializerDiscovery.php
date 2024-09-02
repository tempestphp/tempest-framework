<?php

declare(strict_types=1);

namespace Tempest\Container;

use Tempest\Core\Discovery;
use Tempest\Core\HandlesDiscoveryCache;
use Tempest\Support\Reflection\ClassReflector;

/**
 * @property GenericContainer $container
 */
final readonly class InitializerDiscovery implements Discovery
{
    use HandlesDiscoveryCache;

    public function __construct(
        private Container $container,
    ) {
    }

    public function discover(ClassReflector $class): void
    {
        if (! $class->implements(Initializer::class) && ! $class->implements(DynamicInitializer::class)) {
            return;
        }

        $this->container->addInitializer($class);
    }

    public function createCachePayload(): string
    {
        return serialize(
            [
                'initializers' => $this->container->getInitializers(),
                'dynamic_initializers' => $this->container->getDynamicInitializers(),
            ],
        );
    }

    public function restoreCachePayload(Container $container, string $payload): void
    {
        $data = unserialize($payload, ['allowed_classes' => [
            Initializer::class,
            DynamicInitializer::class,
        ]]);

        $this->container->setInitializers($data['initializers'] ?? []);
        $this->container->setDynamicInitializers($data['dynamic_initializers'] ?? []);
    }
}
