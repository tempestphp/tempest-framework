<?php

declare(strict_types=1);

namespace Tempest\Core;

use Tempest\Container\Container;

/** @phpstan-require-implements \Tempest\Core\Discovery */
trait HandlesDiscoveryCache
{
    public function getCachePath(): string
    {
        $parts = explode('\\', static::class);

        $name = array_pop($parts) . '.cache.php';

        return __DIR__ . '/../../../.cache/tempest/' . $name;
    }

    abstract public function createCachePayload(): string;

    abstract public function restoreCachePayload(Container $container, string $payload): void;

    public function hasCache(): bool
    {
        return file_exists($this->getCachePath());
    }

    public function storeCache(): void
    {
        file_put_contents($this->getCachePath(), $this->createCachePayload());
    }

    public function restoreCache(Container $container): void
    {
        $this->restoreCachePayload($container, file_get_contents($this->getCachePath()));
    }

    public function destroyCache(): void
    {
        @unlink($this->getCachePath());
    }
}
