<?php

declare(strict_types=1);

namespace Tempest\Discovery;

use ReflectionClass;
use Tempest\Container\Container;
use Tempest\Mapper\MapperConfig;
use Tempest\Validation\Inferrer;

final readonly class InferrerDiscovery implements Discovery
{
    private const CACHE_PATH = __DIR__ . '/inferrer-discovery.cache.php';

    public function __construct(private MapperConfig $mapperConfig)
    {
    }

    public function discover(ReflectionClass $class): void
    {
        if (! $class->isInstantiable()) {
            return;
        }

        if (! $class->implementsInterface(Inferrer::class)) {
            return;
        }

        $this->mapperConfig->addInferrer(
            $class->newInstanceWithoutConstructor(),
        );
    }

    public function hasCache(): bool
    {
        return file_exists(self::CACHE_PATH);
    }

    public function storeCache(): void
    {
        file_put_contents(self::CACHE_PATH, serialize($this->mapperConfig->inferrers));
    }

    public function restoreCache(Container $container): void
    {
        $inferrers = unserialize(file_get_contents(self::CACHE_PATH));

        $this->mapperConfig->inferrers = $inferrers;
    }

    public function destroyCache(): void
    {
        @unlink(self::CACHE_PATH);
    }
}
