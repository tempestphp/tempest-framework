<?php

declare(strict_types=1);

use Tempest\Database\DatabaseConfig;
use Tempest\Database\Drivers\SQLiteDriver;

return new DatabaseConfig(
    driver: new SQLiteDriver(
        path: __DIR__ . '/../database.sqlite',
    ),
);
