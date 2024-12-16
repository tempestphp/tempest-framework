<?php

declare(strict_types=1);

use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Tempest\Cache\CacheConfig;

return new CacheConfig(
    projectCachePool: new FilesystemAdapter(
        directory: __DIR__ . '/../../../../.cache',
    ),
);
