<?php

declare(strict_types=1);

use Rector\Caching\ValueObject\Storage\MemoryCacheStorage;
use Rector\Config\RectorConfig;
use Tempest\Upgrade\Set\TempestSetList;

return RectorConfig::configure()
    ->withSets([TempestSetList::TEMPEST_310])
    ->withCache(cacheClass: MemoryCacheStorage::class);
