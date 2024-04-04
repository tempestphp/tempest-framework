<?php

declare(strict_types=1);

namespace Tempest\Discovery;

use ReflectionClass;
use Tempest\CoreConfig;
use Tempest\Container\Container;

final readonly class DiscoveryDiscovery implements Discovery
{
    public const CACHE_PATH = __DIR__ . '/discovery-discovery.cache.php';

    public function __construct(
        private CoreConfig $coreConfig,
    ) {
    }

    public function discover(ReflectionClass $class): void
    {
        if (
            ! $class->isInstantiable()
            || ! $class->implementsInterface(Discovery::class)
            || $class->getName() === self::class
        ) {
            return;
        }

        $this->coreConfig->discoveryClasses[] = $class->getName();
    }

    public function hasCache(): bool
    {
        return file_exists(self::CACHE_PATH);
    }

    public function storeCache(): void
    {
        file_put_contents(self::CACHE_PATH, serialize($this->coreConfig->discoveryClasses));
    }

    public function restoreCache(Container $container): void
    {
        $discoveryClasses = unserialize(file_get_contents(self::CACHE_PATH));

        $this->coreConfig->discoveryClasses = $discoveryClasses;
    }

    public function destroyCache(): void
    {
        @unlink(self::CACHE_PATH);
    }
}
