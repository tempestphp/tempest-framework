<?php

declare(strict_types=1);

namespace Tempest\Discovery;

use ReflectionClass;
use Tempest\Container\Container;
use Tempest\Mapper\MapperConfig;
use Tempest\ORM\DynamicCaster;

final readonly class DynamicCasterDiscovery implements Discovery
{
    private const CACHE_PATH = __DIR__ . '/dynamic-casters-discovery.cache.php';

    public function __construct(private MapperConfig $mapperConfig)
    {
    }

    public function discover(ReflectionClass $class): void
    {
        if (! $class->isInstantiable()) {
            return;
        }

        if (! $class->implementsInterface(DynamicCaster::class)) {
            return;
        }

        $this->mapperConfig->addCaster(
            $class->newInstanceWithoutConstructor(),
        );
    }

    public function hasCache(): bool
    {
        return file_exists(self::CACHE_PATH);
    }

    public function storeCache(): void
    {
        file_put_contents(self::CACHE_PATH, serialize($this->mapperConfig->casters));
    }

    public function restoreCache(Container $container): void
    {
        $casters = unserialize(file_get_contents(self::CACHE_PATH));

        $this->mapperConfig->casters = $casters;
    }

    public function destroyCache(): void
    {
        @unlink(self::CACHE_PATH);
    }
}
