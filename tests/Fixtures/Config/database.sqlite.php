<?php

declare(strict_types=1);

use Tempest\Database\Config\SQLiteConfig;

use function Tempest\env;

return new SQLiteConfig(
    path: __DIR__ . '/../database' . env('TEST_TOKEN', 'default') . '.sqlite',
);
