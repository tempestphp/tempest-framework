<?php

use Tempest\AppConfig;
use Tempest\Application\Environment;
use Tempest\Tempest;
use function Tempest\env;

require_once __DIR__ . '/../vendor/autoload.php';

Tempest::boot(__DIR__ . '/../')->http()->run();

exit;