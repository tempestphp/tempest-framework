<?php

use Tempest\Database\DatabaseConfig;
use Tempest\Database\SQLiteDriver;

return new DatabaseConfig(
    driver: new SQLiteDriver(
        path: __DIR__ . '/../database.sqlite',
    ),
);