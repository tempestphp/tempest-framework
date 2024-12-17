<?php

declare(strict_types=1);

use Tempest\Database\Connections\MySqlConnection;
use Tempest\Database\DatabaseConfig;
use function Tempest\env;

return new DatabaseConfig(
    connection: new MySqlConnection(
        host: env('DB_HOST'),
        port: env('DB_PORT'),
        username: env('DB_USERNAME'),
        password: env('DB_PASSWORD'),
        database: env('DB_DATABASE'),
    ),
);
