<?php

declare(strict_types=1);

use Tempest\Database\Config\PostgresConfig;
use function Tempest\env;


return new PostgresConfig(
    username: env('POSTGRES_USER', 'postgres'),
    password: env('POSTGRES_PASSWORD', ''),
);
