<?php

declare(strict_types=1);

use Tempest\Database\Config\SQLiteConfig;

return new SQLiteConfig(
    path: __DIR__ . '/../database.sqlite',
);
