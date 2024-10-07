<?php

namespace Tempest\View;

use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Cache\Adapter\PhpFilesAdapter;
use Tempest\Cache\Cache;
use Tempest\Cache\IsCache;

final readonly class ViewCache implements Cache
{
    use IsCache;

    protected function getCachePool(): CacheItemPoolInterface
    {
        return new PhpFilesAdapter(
            namespace: '',
            defaultLifetime: 0,
            directory: null,
        );
    }
}