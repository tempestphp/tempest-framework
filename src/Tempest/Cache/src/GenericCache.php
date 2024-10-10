<?php

declare(strict_types=1);

namespace Tempest\Cache;

use Psr\Cache\CacheItemPoolInterface;

final readonly class GenericCache implements Cache
{
    use IsCache;

    public function __construct(
        private CacheItemPoolInterface $pool,
    ) {
    }

    protected function getCachePool(): CacheItemPoolInterface
    {
        return $this->pool;
    }
}
