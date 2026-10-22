<?php

declare(strict_types=1);

use Tempest\Database\Config\MysqlConfig;

use function Tempest\env;

return new MysqlConfig(
    host: '127.0.0.1',
    database: 'app' . env('TEST_TOKEN', ''),
);
