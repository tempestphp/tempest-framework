<?php

declare(strict_types=1);

namespace Tempest\View;

use Closure;

use function Tempest\internal_storage_path;
use function Tempest\Support\path;

final class ViewCache
{
    public function __construct(
        public bool $enabled = false,
        private ?ViewCachePool $pool = null,
    ) {
        $this->pool ??= new ViewCachePool(
            directory: internal_storage_path('cache/views'),
        );
    }

    public function clear(): void
    {
        $this->pool->clear();
    }

    public function getCachedViewPath(string $path, Closure $compiledView): string
    {
        $cacheKey = (string) crc32($path);

        $cacheItem = $this->pool->getItem($cacheKey);

        if ($this->enabled === false || $cacheItem->isHit() === false) {
            $cacheItem->set($compiledView());

            $this->pool->save($cacheItem);
        }

        return path($this->pool->directory, $cacheItem->getKey() . '.php')->toString();
    }
}
