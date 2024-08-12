<?php

declare(strict_types=1);

namespace Tempest\Container;

use Tempest\Discovery\Discovery;
use Tempest\Discovery\HandlesDiscoveryCache;
use Tempest\Support\Reflection\ClassReflector;

final readonly class InitializerDiscovery implements Discovery
{
    use HandlesDiscoveryCache;

    public function __construct(
        private Container&GenericContainer $container,
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
        $data = unserialize($payload);

        $this->container->setInitializers($data['initializers'] ?? []);
        $this->container->setDynamicInitializers($data['dynamic_initializers'] ?? []);
    }
}
