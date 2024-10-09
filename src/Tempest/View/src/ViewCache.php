<?php

declare(strict_types=1);

namespace Tempest\View;

use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Cache\Adapter\PhpFilesAdapter;
use Tempest\Cache\Cache;
use Tempest\Cache\CacheConfig;
use Tempest\Cache\IsCache;

final class ViewCache implements Cache
{
    use IsCache;

    private readonly PhpFilesAdapter $cachePool;

    public function __construct(
        private readonly CacheConfig $cacheConfig
    ) {
        $this->cachePool = new PhpFilesAdapter(
            namespace: '',
            defaultLifetime: 0,
            directory: null,
        );
    }

    protected function getCachePool(): CacheItemPoolInterface
    {
        return $this->cachePool;
    }

    protected function isEnabled(): bool
    {
        return $this->cacheConfig->enabled;
    }
}
