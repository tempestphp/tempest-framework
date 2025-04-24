<?php

use Tempest\Storage\Config\LocalStorageConfig;

use function Tempest\internal_storage_path;

return new LocalStorageConfig(
    path: internal_storage_path('storage'),
);
