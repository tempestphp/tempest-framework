<?php

namespace Tempest\Cache\Config;

use Psr\Cache\CacheItemPoolInterface;
use Tempest\Container\HasTag;

interface CacheConfig extends HasTag
{
    /**
     * Creates the adapter.
     */
    public function createAdapter(): CacheItemPoolInterface;
}
