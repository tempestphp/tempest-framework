<?php

use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withSets([
        __DIR__ . '/../../src/tempest2.php',
    ]);
