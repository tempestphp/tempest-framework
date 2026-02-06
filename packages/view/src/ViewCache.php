<?php

declare(strict_types=1);

namespace Tempest\View;

use Closure;
use Throwable;

use function Tempest\internal_storage_path;
use function Tempest\Support\path;

final class ViewCache
{
    public function __construct(
        public bool $enabled = false,
        private ?ViewCachePool $pool = null,
    ) {
        $this->pool ??= new ViewCachePool(
            directory: self::getCachePath(),
        );
    }

    public static function create(bool $enabled = true, ?string $path = null): self
    {
        return new self(
            enabled: $enabled,
            pool: new ViewCachePool($path ?? self::getCachePath()),
        );
    }

    public function clear(): void
    {
        $this->pool->clear();
    }

    public function getCachedViewPath(string $path, Closure $compiledView): string
    {
        $cacheKey = hash('xxh64', $path);

        $cacheItem = $this->pool->getItem($cacheKey);

        if ($this->enabled === false || $cacheItem->isHit() === false) {
            $cacheItem->set($compiledView());

            $this->pool->save($cacheItem);
        }

        return path($this->pool->directory, $cacheItem->getKey() . '.php')->toString();
    }

    private static function getCachePath(): string
    {
        try {
            return internal_storage_path('cache/views');
        } catch (Throwable) {
            return __DIR__ . '/../.tempest/cache';
        }
    }
}
