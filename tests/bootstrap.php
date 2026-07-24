<?php

declare(strict_types=1);

use Tempest\Support\Filesystem;

error_reporting(E_ALL ^ E_DEPRECATED ^ E_USER_DEPRECATED);

require_once __DIR__ . '/../vendor/autoload.php';

$root = dirname(__DIR__);
$token = getenv('TEST_TOKEN');

if ($token === false) {
    passthru('php tempest key:generate --no-override --no-interaction');
    echo PHP_EOL;
    passthru('php tempest discovery:generate --no-interaction');
    echo PHP_EOL;
}

$discoveryStrategy = "{$root}/.tempest/current_discovery_strategy";
$discoveryCache = "{$root}/.tempest/cache/discovery";
$storage = "{$root}/.tempest/test_internal_storage/" . ($token ?: 'default');

if (Filesystem\is_directory("{$storage}/cache")) {
    Filesystem\delete_directory("{$storage}/cache");
}

if (Filesystem\is_file($discoveryStrategy)) {
    Filesystem\copy_directory($discoveryCache, "{$storage}/cache/discovery");
    Filesystem\copy_file($discoveryStrategy, "{$storage}/current_discovery_strategy", overwrite: true);
}
