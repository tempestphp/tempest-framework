<?php

declare(strict_types=1);

namespace Tempest\Discovery;

use ReflectionClass;
use Tempest\AppConfig;
use Tempest\Interface\Container;
use Tempest\Interface\Discovery;

final readonly class DiscoveryDiscovery implements Discovery
{
    public function __construct(
        private AppConfig $appConfig,
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

        $this->appConfig->discoveryClasses[] = $class->getName();
    }

    public function hasCache(): bool
    {
        return false;
    }

    public function storeCache(): void
    {
        // TODO: Implement storeCache() method.
    }

    public function restoreCache(Container $container): void
    {
        // TODO: Implement restoreCache() method.
    }

    public function destroyCache(): void
    {
        // TODO: Implement destroyCache() method.
    }
}
