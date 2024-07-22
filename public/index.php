<?php

use Tempest\Core\Tempest;

require_once __DIR__ . '/../vendor/autoload.php';

Tempest::boot(__DIR__ . '/../')->http()->run();

exit;
