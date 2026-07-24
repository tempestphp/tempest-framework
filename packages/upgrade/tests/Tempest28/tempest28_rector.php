<?php

use Rector\Caching\ValueObject\Storage\MemoryCacheStorage;
use Rector\Config\RectorConfig;
use Tempest\Upgrade\Set\TempestSetList;

return RectorConfig::configure()
    ->withSets([
        TempestSetList::TEMPEST_28,
    ])
    ->withCache(cacheClass: MemoryCacheStorage::class);
