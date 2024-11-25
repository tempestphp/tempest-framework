<?php

declare(strict_types=1);

require_once  __DIR__ . '/../vendor/autoload.php';

passthru('php tempest discovery:generate');
