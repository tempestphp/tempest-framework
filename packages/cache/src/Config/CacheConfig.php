<?php

namespace Tempest\Cache\Config;

use Psr\Cache\CacheItemPoolInterface;
use Tempest\Container\Container;
use Tempest\Container\HasTag;

interface CacheConfig extends HasTag
{
    /**
     * Creates the adapter.
     */
    public function createAdapter(Container $container): CacheItemPoolInterface;
}
