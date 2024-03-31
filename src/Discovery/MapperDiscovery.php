<?php

declare(strict_types=1);

namespace Tempest\Discovery;

use ReflectionClass;
use Tempest\Container\Container;
use Tempest\Mapper\Mapper;
use Tempest\Mapper\MapperConfig;

final readonly class MapperDiscovery implements Discovery
{
    private const CACHE_PATH = __DIR__ . '/mapper-discovery.cache.php';

    public function __construct(private MapperConfig $mapperConfig)
    {
    }

    public function discover(ReflectionClass $class): void
    {
        if (! $class->isInstantiable()) {
            return;
        }

        if (! $class->implementsInterface(Mapper::class)) {
            return;
        }

        $this->mapperConfig->addMapper(
            $class->newInstance()
        );
    }

    public function hasCache(): bool
    {
        return file_exists(self::CACHE_PATH);
    }

    public function storeCache(): void
    {
        file_put_contents(self::CACHE_PATH, serialize($this->mapperConfig->mappers));
    }

    public function restoreCache(Container $container): void
    {
        $mappers = unserialize(file_get_contents(self::CACHE_PATH));

        $this->mapperConfig->mappers = $mappers;
    }

    public function destroyCache(): void
    {
        @unlink(self::CACHE_PATH);
    }
}
