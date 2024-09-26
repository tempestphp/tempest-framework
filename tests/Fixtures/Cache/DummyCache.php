<?php

declare(strict_types=1);

namespace Tests\Tempest\Fixtures\Cache;

use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Cache\Adapter\NullAdapter;
use Tempest\Cache\Cache;
use Tempest\Cache\IsCache;

final class DummyCache implements Cache
{
    use IsCache;

    private CacheItemPoolInterface $pool;

    public bool $cleared = false;

    public function __construct()
    {
        $this->pool = new NullAdapter();
    }

    protected function getCachePool(): CacheItemPoolInterface
    {
        return $this->pool;
    }

    public function clear(): void
    {
        $this->cleared = true;
    }
}
