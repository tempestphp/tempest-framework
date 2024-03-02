<?php

declare(strict_types=1);

namespace Tempest\Discovery;

use ReflectionClass;
use Tempest\Container\Container;
use Tempest\Container\Initializer;

final readonly class InitializerDiscovery implements Discovery
{
    private const string CACHE_PATH = __DIR__ . '/initializer-discovery.cache.php';

    public function __construct(
        private Container $container,
    ) {
    }

    public function discover(ReflectionClass $class): void
    {
        if (
            ! $class->isInstantiable()
            || ! $class->implementsInterface(Initializer::class)
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
        file_put_contents(self::CACHE_PATH, serialize($this->container->initializers));
    }

    public function restoreCache(Container $container): void
    {
        $initializers = unserialize(file_get_contents(self::CACHE_PATH));

        $this->container->initializers = $initializers;
    }

    public function destroyCache(): void
    {
        @unlink(self::CACHE_PATH);
    }
}
