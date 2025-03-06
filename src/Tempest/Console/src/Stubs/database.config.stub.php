<?php

declare(strict_types=1);

use Tempest\Database\Config\MysqlConfig;
use function Tempest\env;

return new MysqlConfig(
    host: env('DB_HOST'),
    port: env('DB_PORT'),
    username: env('DB_USERNAME'),
    password: env('DB_PASSWORD'),
    database: env('DB_DATABASE'),
);
