<?php

declare(strict_types=1);

namespace Tempest\Discovery;

use ReflectionClass;
use Tempest\Container\Container;
use Tempest\Validation\Inferrer;
use Tempest\Validation\InferrerConfig;

final readonly class InferrerDiscovery implements Discovery
{
    private const CACHE_PATH = __DIR__ . '/inferrer-discovery.cache.php';

    public function __construct(private InferrerConfig $inferrerConfig)
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

        $this->inferrerConfig->addInferrer(
            $class->newInstanceWithoutConstructor(),
        );
    }

    public function hasCache(): bool
    {
        return file_exists(self::CACHE_PATH);
    }

    public function storeCache(): void
    {
        file_put_contents(self::CACHE_PATH, serialize($this->inferrerConfig->inferrers));
    }

    public function restoreCache(Container $container): void
    {
        $inferrers = unserialize(file_get_contents(self::CACHE_PATH));

        $this->inferrerConfig->inferrers = $inferrers;
    }

    public function destroyCache(): void
    {
        @unlink(self::CACHE_PATH);
    }
}
