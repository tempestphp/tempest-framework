<?php

declare(strict_types=1);

use Tempest\Database\Connections\MySqlConnection;
use Tempest\Database\DatabaseConfig;

return new DatabaseConfig(
    connection: new MySqlConnection(),
);
