<?php

declare(strict_types=1);

namespace Tempest\Container;

use ReflectionClass;
use Tempest\Discovery\Discovery;

final readonly class InitializerDiscovery implements Discovery
{
    private const string CACHE_PATH = __DIR__ . '/../../../.cache/tempest/initializer-discovery.cache.php';

    public function __construct(
        private Container&GenericContainer $container,
    ) {
    }

    public function discover(ReflectionClass $class): void
    {
        if (! $class->isInstantiable()) {
            return;
        }

        if (
            ! $class->implementsInterface(Initializer::class)
            && ! $class->implementsInterface(DynamicInitializer::class)
        ) {
            return;
        }

        $this->container->addInitializer($class);
    }

    public function hasCache(): bool
    {
        return file_exists(self::CACHE_PATH);
    }

    public function storeCache(): void
    {
        file_put_contents(self::CACHE_PATH, serialize(
            [
                'initializers' => $this->container->getInitializers(),
                'dynamic_initializers' => $this->container->getDynamicInitializers(),
            ],
        ));
    }

    public function restoreCache(Container $container): void
    {
        $data = unserialize(file_get_contents(self::CACHE_PATH));

        $this->container->setInitializers($data['initializers'] ?? []);
        $this->container->setDynamicInitializers($data['dynamic_initializers'] ?? []);
    }

    public function destroyCache(): void
    {
        @unlink(self::CACHE_PATH);
    }
}
