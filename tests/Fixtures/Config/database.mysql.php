<?php

declare(strict_types=1);

use Tempest\Database\Config\MysqlConfig;

use function Tempest\env;

return new MysqlConfig(
    database: 'app' . env('TEST_TOKEN', ''),
);
