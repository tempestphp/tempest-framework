<?php

use Tempest\Application\HttpApplication;

require_once __DIR__ . '/../vendor/autoload.php';

(new HttpApplication(__DIR__ . '/../app'))->run();

exit;