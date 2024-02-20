<?php

declare(strict_types=1);

use Tempest\Discovery\DiscoveryDiscovery;

require_once __DIR__ . '/../vendor/autoload.php';

@unlink(DiscoveryDiscovery::CACHE_PATH);
