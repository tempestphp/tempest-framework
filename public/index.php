<?php

use Tempest\Tempest;

require_once __DIR__ . '/../vendor/autoload.php';

Tempest::boot(dirname(__DIR__))->http()->run();

exit;
