<?php

declare(strict_types=1);

namespace Tempest\Discovery;

use ReflectionClass;
use Tempest\Container\Container;
use Tempest\Mapper\Caster;
use Tempest\Mapper\Mapper;
use Tempest\Mapper\MapperConfig;

final readonly class MapperDiscovery implements Discovery
{
    private const string CACHE_PATH = __DIR__ . '/mapper-discovery.cache.php';

    public function __construct(
        private MapperConfig $config,
    ) {}

    public function discover(ReflectionClass $class): void
    {
        if (! $class->isInstantiable()) {
            return;
        }

        if ($class->implementsInterface(Mapper::class)) {
            $this->config->mappers[] = $class->getName();
        } elseif ($class->implementsInterface(Caster::class)) {
            $this->config->casters[] = $class->getName();
        }
    }

    public function hasCache(): bool
    {
        return file_exists(self::CACHE_PATH);
    }

    public function storeCache(): void
    {
        file_put_contents(self::CACHE_PATH, serialize([
            'mappers' => $this->config->mappers,
            'casters' => $this->config->casters,
        ]));
    }

    public function restoreCache(Container $container): void
    {
        [
            'mappers' => $mappers,
            'casters' => $casters,
        ] = unserialize(file_get_contents(self::CACHE_PATH));

        $this->config->mappers = $mappers;
        $this->config->casters = $casters;
    }

    public function destroyCache(): void
    {
        @unlink(self::CACHE_PATH);
    }
}
