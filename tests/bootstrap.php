<?php

declare(strict_types=1);
use Tempest\Testing\BypassMock\Bypass;

require_once  __DIR__ . '/../vendor/autoload.php';

passthru('./tempest discovery:clear');
Bypass::enable();
