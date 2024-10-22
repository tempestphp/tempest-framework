<?php

declare(strict_types=1);

use Tempest\Database\Connections\SQLiteConnection;
use Tempest\Database\DatabaseConfig;

return new DatabaseConfig(
    connection: new SQLiteConnection(
        path: __DIR__ . '/../database.sqlite',
    ),
);
