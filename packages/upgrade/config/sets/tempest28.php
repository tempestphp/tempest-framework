<?php

use Rector\Config\RectorConfig;
use Tempest\Upgrade\Tempest28\WriteableRouteRector;

return static function (RectorConfig $config): void {
    $config->importNames();
    $config->importShortClasses();

    $config->rule(WriteableRouteRector::class);
};
