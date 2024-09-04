<?php

declare(strict_types=1);

use Tempest\Database\Connections\PostgresConnection;
use Tempest\Database\DatabaseConfig;

return new DatabaseConfig(
    connection: new PostgresConnection(),
);
