<?php

declare(strict_types=1);

use Tempest\Database\Config\SQLiteConfig;

use function Tempest\internal_storage_path;

return new SQLiteConfig(
    path: internal_storage_path('database.sqlite'),
);
