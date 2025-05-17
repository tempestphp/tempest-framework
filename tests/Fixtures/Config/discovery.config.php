<?php

use Tempest\Core\DiscoveryConfig;
use Tests\Tempest\Fixtures\GlobalHiddenDiscovery;

return new DiscoveryConfig()
    ->skipClasses(GlobalHiddenDiscovery::class)
    ->skipPaths(__DIR__ . '/../../Fixtures/GlobalHiddenPathDiscovery.php');
