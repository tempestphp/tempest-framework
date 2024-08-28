<?php

declare(strict_types=1);

use Tempest\Database\DatabaseConfig;
use Tempest\Database\Drivers\MySqlDriver;

return new DatabaseConfig(
    driver: new MySqlDriver(),
);
