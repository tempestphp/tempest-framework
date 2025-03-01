<?php

declare(strict_types=1);

error_reporting(E_ALL ^ E_DEPRECATED ^ E_USER_DEPRECATED);

require_once __DIR__ . '/../vendor/autoload.php';

passthru('php tempest discovery:generate --no-interaction');
echo PHP_EOL;
