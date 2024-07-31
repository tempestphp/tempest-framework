<?php

declare(strict_types=1);

namespace Tempest\Mapper;

use ReflectionClass;
use Tempest\Container\Container;
use Tempest\Discovery\Discovery;

final readonly class MapperDiscovery implements Discovery
{
    private const string CACHE_PATH = __DIR__ . '/../../../.cache/tempest/mapper-discovery.cache.php';

    public function __construct(
        private MapperConfig $config,
    ) {
    }

    public function discover(ReflectionClass $class): void
    {
        if (! $class->isInstantiable()) {
            return;
        }

        if ($class->implementsInterface(Mapper::class)) {
            $this->config->mappers[] = $class->getName();
        }
    }

    public function hasCache(): bool
    {
        return file_exists(self::CACHE_PATH);
    }

    public function storeCache(): void
    {
        file_put_contents(self::CACHE_PATH, serialize($this->config->mappers));
    }

    public function restoreCache(Container $container): void
    {
        $mappers = unserialize(file_get_contents(self::CACHE_PATH));

        $this->config->mappers = $mappers;
    }

    public function destroyCache(): void
    {
        @unlink(self::CACHE_PATH);
    }
}
