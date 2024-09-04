<?php

declare(strict_types=1);

use Tempest\Http\HttpApplication;

require_once __DIR__ . '/../vendor/autoload.php';

HttpApplication::boot(__DIR__ . '/../')->run();

exit;
