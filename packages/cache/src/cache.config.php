<?php

use Tempest\Cache\Config\FilesystemCacheConfig;

use function Tempest\internal_storage_path;

return new FilesystemCacheConfig(
    directory: internal_storage_path('/cache/project'),
);
