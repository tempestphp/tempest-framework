<?php

declare(strict_types=1);

use Tempest\Database\Config\SQLiteConfig;
use Tempest\Support\Filesystem;

use function Tempest\env;

$directory = dirname(__DIR__, 3) . '/.tempest/test-databases';

Filesystem\ensure_directory_exists($directory);

return new SQLiteConfig(
    path: $directory . '/database' . env('TEST_TOKEN', 'default') . '.sqlite',
);
